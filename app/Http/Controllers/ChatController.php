<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display chat index page based on user role
     */
    public function index()
    {
        try {
            $user = auth()->user();
            
            if ($user->role === 'nurse') {
                // Nurse chat index - show all conversations
                $conversations = ChatConversation::where('nurse_id', $user->id)
                    ->with(['student', 'lastMessage'])
                    ->withCount(['messages as unread_count' => function ($q) use ($user) {
                        $q->where('sender_id', '!=', $user->id)
                          ->where('is_read', false);
                    }])
                    ->orderBy('last_message_at', 'desc')
                    ->get();

                return view('chat.nurse.index', compact('conversations'));
                
            } else {
                // Student - show chat index page with existing conversations
                $conversations = ChatConversation::where('student_id', $user->id)
                    ->with(['nurse', 'lastMessage'])
                    ->withCount(['messages as unread_count' => function ($q) use ($user) {
                        $q->where('sender_id', '!=', $user->id)
                          ->where('is_read', false);
                    }])
                    ->orderBy('last_message_at', 'desc')
                    ->get();

                // Check if any nurses exist in the system
                $nurseExists = User::where('role', 'nurse')->exists();
                
                return view('chat.student.index', compact('conversations', 'nurseExists'));
            }
            
        } catch (\Exception $e) {
            Log::error('Chat index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load conversations: ' . $e->getMessage());
        }
    }

    /**
     * Search students (for nurses)
     */
    public function searchStudents(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            // Minimum 2 characters
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'students' => [],
                    'message' => 'Please enter at least 2 characters'
                ]);
            }

            $students = User::where('role', 'student')
                ->where(function($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('student_id', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->select('id', 'first_name', 'last_name', 'student_id', 'course', 'year_level')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(15)
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students->map(function($student) {
                    return [
                        'id' => $student->id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'student_id' => $student->student_id,
                        'course' => $student->course ?? 'N/A',
                        'year_level' => $student->year_level ?? 'N/A'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Search students error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage(),
                'students' => []
            ], 500);
        }
    }

    /**
     * Search nurses (for students)
     */
    public function searchNurses(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            $nurses = User::where('role', 'nurse')
                ->where(function($q) use ($query) {
                    if ($query) {
                        $q->where('first_name', 'like', "%{$query}%")
                          ->orWhere('last_name', 'like', "%{$query}%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                    }
                })
                ->select('id', 'first_name', 'last_name', 'email')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'nurses' => $nurses
            ]);

        } catch (\Exception $e) {
            Log::error('Search nurses error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage(),
                'nurses' => []
            ], 500);
        }
    }

    /**
     * Get or create conversation
     */
    public function getOrCreateConversation(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $user = auth()->user();
            
            if ($user->role === 'nurse') {
                // Nurse starting conversation with student
                $request->validate([
                    'student_id' => 'required|exists:users,id'
                ]);
                
                $student = User::where('id', $request->student_id)
                    ->where('role', 'student')
                    ->first();
                
                if (!$student) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Student not found'
                    ], 404);
                }
                
                $conversation = ChatConversation::firstOrCreate(
                    [
                        'nurse_id' => $user->id,
                        'student_id' => $student->id
                    ],
                    [
                        'last_message_at' => now()
                    ]
                );
                
            } else {
                // Student starting conversation
                // Try to find existing conversation first
                $existingConversation = ChatConversation::where('student_id', $user->id)
                    ->first();
                
                if ($existingConversation) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'conversation_id' => $existingConversation->id,
                        'redirect_url' => route('chat.conversation', $existingConversation->id)
                    ]);
                }
                
                // Check if any nurses exist
                $nurseExists = User::where('role', 'nurse')->exists();
                
                if (!$nurseExists) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'No nurses are currently available in the system. Please contact the clinic directly for assistance.',
                        'redirect_url' => route('chat.index')
                    ], 404);
                }
                
                // Find available nurse (try with is_active first, then without)
                $nurse = User::where('role', 'nurse')
                    ->when(
                        \Schema::hasColumn('users', 'is_active'),
                        function ($query) {
                            return $query->where('is_active', true);
                        }
                    )
                    ->first();
                
                if (!$nurse) {
                    // Fallback: get any nurse
                    $nurse = User::where('role', 'nurse')->first();
                }
                
                if (!$nurse) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'No nurse available at the moment. Please try again later or contact the clinic directly.',
                        'redirect_url' => route('chat.index')
                    ], 404);
                }
                
                $conversation = ChatConversation::create([
                    'nurse_id' => $nurse->id,
                    'student_id' => $user->id,
                    'last_message_at' => now()
                ]);
                
                // Add a welcome message
                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $nurse->id,
                    'sender_type' => 'nurse',
                    'message' => "Hello! I'm Nurse. How can I help you today?",
                    'is_read' => false
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'redirect_url' => route('chat.conversation', $conversation->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Get or create conversation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create conversation. Please try again.'
            ], 500);
        }
    }

    /**
     * Show conversation
     */
    public function show($conversationId)
    {
        try {
            $user = auth()->user();
            
            $conversation = ChatConversation::with(['nurse', 'student'])
                ->find($conversationId);

            if (!$conversation) {
                Log::error('Conversation not found: ' . $conversationId);
                return redirect()->route('chat.index')
                    ->with('error', 'Conversation not found. Please start a new conversation.');
            }

            // Check if user is participant
            if (!$conversation->isParticipant($user->id)) {
                Log::error('Unauthorized access attempt to conversation: ' . $conversationId . ' by user: ' . $user->id);
                abort(403, 'Unauthorized access to this conversation');
            }

            // Get messages
            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark messages as read
            $conversation->markMessagesAsRead($user->id);

            // Get other participant
            $otherParticipant = $conversation->getOtherParticipant($user->id);
            
            if (!$otherParticipant) {
                Log::error('Other participant not found for conversation: ' . $conversationId);
                return redirect()->route('chat.index')
                    ->with('error', 'Unable to load conversation. Please try again.');
            }

            if ($user->role === 'nurse') {
                return view('chat.nurse.conversation', compact('conversation', 'messages', 'otherParticipant'));
            } else {
                return view('chat.student.conversation', compact('conversation', 'messages', 'otherParticipant'));
            }

        } catch (\Exception $e) {
            Log::error('Show conversation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('chat.index')
                ->with('error', 'Failed to load conversation. Please try again.');
        }
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:5000'
            ]);

            $user = auth()->user();
            
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation not found'
                ], 404);
            }

            // Check if user is participant
            if (!$conversation->isParticipant($user->id)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            // Create message
            $message = $conversation->addMessage(
                $user->id,
                $request->message,
                $user->role
            );

            // Load sender relationship
            $message->load('sender');

            return response()->json([
                'success' => true,
                'message' => $message->toApiResponse()
            ]);

        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages
     */
    public function getMessages($conversationId)
    {
        try {
            $user = auth()->user();
            
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation not found'
                ], 404);
            }

            if (!$conversation->isParticipant($user->id)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'messages' => $messages->map->toApiResponse()
            ]);

        } catch (\Exception $e) {
            Log::error('Get messages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load messages'
            ], 500);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($conversationId)
    {
        try {
            $user = auth()->user();
            
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation not found'
                ], 404);
            }

            if (!$conversation->isParticipant($user->id)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $conversation->markMessagesAsRead($user->id);

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark as read'
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        try {
            $user = auth()->user();
            
            $unreadCount = ChatMessage::whereHas('conversation', function($q) use ($user) {
                    $q->where('nurse_id', $user->id)
                      ->orWhere('student_id', $user->id);
                })
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            Log::error('Get unread count error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Check if nurses are available
     */
    public function checkNurseAvailability()
    {
        try {
            $nurseExists = User::where('role', 'nurse')->exists();
            $activeNurseCount = User::where('role', 'nurse')
                ->when(
                    \Schema::hasColumn('users', 'is_active'),
                    function ($query) {
                        return $query->where('is_active', true);
                    }
                )
                ->count();

            return response()->json([
                'success' => true,
                'nurse_exists' => $nurseExists,
                'active_nurse_count' => $activeNurseCount,
                'has_available_nurses' => $activeNurseCount > 0 || $nurseExists
            ]);

        } catch (\Exception $e) {
            Log::error('Check nurse availability error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'nurse_exists' => false,
                'has_available_nurses' => false
            ]);
        }
    }
}