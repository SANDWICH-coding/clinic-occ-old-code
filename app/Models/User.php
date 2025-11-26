<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'department',
        'student_id',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'course',
        'year_level',
        'section',
        'academic_year',
        'year_level_updated_at',
        'must_change_password',
        'password_changed_at',
        'is_online',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'must_change_password' => 'boolean',
        'year_level_updated_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = [
        'age',
        'formatted_phone',
        'academic_info',
        'requires_year_level_update',
        'emergency_contacts',
        'allowed_courses',
        'department_name',
        'full_name',
        'online_status',
        'unread_messages_count',
    ];

    // Role constants
    public const ROLE_STUDENT = 'student';
    public const ROLE_NURSE = 'nurse';
    public const ROLE_DEAN = 'dean';
    public const ROLE_DEAN_BSIT = 'dean_bsit';
    public const ROLE_DEAN_BSBA = 'dean_bsba';
    public const ROLE_DEAN_EDUC = 'dean_educ';

    // Department constants
    public const DEPARTMENT_BSIT = 'BSIT';
    public const DEPARTMENT_BSBA = 'BSBA';
    public const DEPARTMENT_EDUC = 'EDUC';

    // Role check helpers
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isNurse(): bool
    {
        return $this->role === self::ROLE_NURSE;
    }

    public function isDean(): bool
    {
        return in_array($this->role, [
            self::ROLE_DEAN,
            self::ROLE_DEAN_BSIT,
            self::ROLE_DEAN_BSBA,
            self::ROLE_DEAN_EDUC
        ]);
    }

    public function isSpecificDean(): bool
    {
        return in_array($this->role, [
            self::ROLE_DEAN_BSIT,
            self::ROLE_DEAN_BSBA,
            self::ROLE_DEAN_EDUC
        ]);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_NURSE, self::ROLE_DEAN, self::ROLE_DEAN_BSIT, self::ROLE_DEAN_BSBA, self::ROLE_DEAN_EDUC]);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // Dashboard Routing Methods
    /**
     * Get the appropriate dashboard route based on user role
     */
    public function getDashboardRoute(): string
    {
        return match($this->role) {
            self::ROLE_STUDENT => 'dashboard.student',
            self::ROLE_NURSE => 'dashboard.nurse',
            self::ROLE_DEAN_BSIT => 'dashboard.dean-bsit',
            self::ROLE_DEAN_BSBA => 'dashboard.dean-bsba',
            self::ROLE_DEAN_EDUC => 'dashboard.dean-educ',
            self::ROLE_DEAN => 'dashboard.dean', // This will now redirect to specific department
            default => 'dashboard.student',
        };
    }

    /**
     * Get the dean's specific department for dashboard routing
     */
    public function getDeanDepartmentForDashboard(): ?string
    {
        if (!$this->isDean()) {
            return null;
        }

        // For specific dean roles
        if ($this->isSpecificDean()) {
            return match($this->role) {
                self::ROLE_DEAN_BSIT => 'BSIT',
                self::ROLE_DEAN_BSBA => 'BSBA', 
                self::ROLE_DEAN_EDUC => 'EDUC',
                default => null,
            };
        }

        // For general dean role, determine from email or department
        $email = strtolower($this->email);
        
        if (str_contains($email, 'bsit')) {
            return 'BSIT';
        }
        
        if (str_contains($email, 'bsba')) {
            return 'BSBA';
        }
        
        if (str_contains($email, 'educ')) {
            return 'EDUC';
        }

        // Fallback to department field
        return $this->department;
    }

    /**
     * Check if user can be redirected to a specific department dashboard
     */
    public function canRedirectToDepartmentDashboard(): bool
    {
        if (!$this->isDean()) {
            return false;
        }

        $department = $this->getDeanDepartmentForDashboard();
        return in_array($department, ['BSIT', 'BSBA', 'EDUC']);
    }

    /**
     * Get the redirect URL for dean dashboard
     */
    public function getDeanDashboardRedirectUrl(): string
    {
        if (!$this->isDean()) {
            return route('dashboard.student');
        }

        $department = $this->getDeanDepartmentForDashboard();
        
        return match($department) {
            'BSIT' => route('dashboard.dean-bsit'),
            'BSBA' => route('dashboard.dean-bsba'),
            'EDUC' => route('dashboard.dean-educ'),
            default => route('dashboard.dean-bsit'), // Default fallback
        };
    }

    // Department methods
    protected function allowedCourses(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->isDean()) {
                    return [];
                }

                // For specific dean roles
                if ($this->isSpecificDean()) {
                    return match ($this->role) {
                        self::ROLE_DEAN_BSIT => ['BSIT'],
                        self::ROLE_DEAN_BSBA => ['BSBA', 'BSBA-MM', 'BSBA-FM'],
                        self::ROLE_DEAN_EDUC => ['BSED', 'BEED'],
                        default => [],
                    };
                }

                // For general dean role with department field
                if ($this->role === self::ROLE_DEAN && $this->department) {
                    return match ($this->department) {
                        self::DEPARTMENT_BSIT => ['BSIT'],
                        self::DEPARTMENT_BSBA => ['BSBA', 'BSBA-MM', 'BSBA-FM'],
                        self::DEPARTMENT_EDUC => ['BSED', 'BEED'],
                        default => [],
                    };
                }

                return [];
            }
        );
    }

    public function isDeanOf($department): bool
    {
        if (!$this->isDean()) {
            return false;
        }

        // For specific dean roles
        if ($this->isSpecificDean()) {
            $deanDepartment = match ($this->role) {
                self::ROLE_DEAN_BSIT => 'BSIT',
                self::ROLE_DEAN_BSBA => 'BSBA',
                self::ROLE_DEAN_EDUC => 'EDUC',
                default => null,
            };
            return $deanDepartment === $department;
        }

        // For general dean role
        return $this->role === self::ROLE_DEAN && $this->department === $department;
    }

    public function canAccessStudent($student): bool
    {
        if ($this->role === self::ROLE_NURSE) {
            return true;
        }

        if ($this->isDean()) {
            $allowedCourses = $this->allowed_courses;
            return in_array($student->course, $allowedCourses);
        }

        return false;
    }

    protected function departmentName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isSpecificDean()) {
                    return match ($this->role) {
                        self::ROLE_DEAN_BSIT => 'Information Technology',
                        self::ROLE_DEAN_BSBA => 'Business Administration',
                        self::ROLE_DEAN_EDUC => 'Education',
                        default => null,
                    };
                }

                if (!$this->department) {
                    return null;
                }

                return match ($this->department) {
                    self::DEPARTMENT_BSIT => 'Information Technology',
                    self::DEPARTMENT_BSBA => 'Business Administration',
                    self::DEPARTMENT_EDUC => 'Education',
                    default => $this->department,
                };
            }
        );
    }

    // Get dean department for specific dean roles
    public function getDeanDepartment()
    {
        if ($this->isSpecificDean()) {
            return match ($this->role) {
                self::ROLE_DEAN_BSIT => 'BSIT',
                self::ROLE_DEAN_BSBA => 'BSBA',
                self::ROLE_DEAN_EDUC => 'EDUC',
                default => null,
            };
        }

        return $this->department;
    }

    // Relationships
    public function student()
    {
        return $this->hasOne(Student::class, 'student_id', 'student_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function createdMedicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'created_by');
    }

    public function symptomLogs(): HasMany
    {
        return $this->hasMany(SymptomLog::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function nurseAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'nurse_id');
    }

    public function cancelledAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'cancelled_by');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'student_id');
    }

    // Chat Relationships
    public function nurseConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'nurse_id');
    }

    public function studentConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'student_id');
    }

    public function allConversations()
    {
        return ChatConversation::where('nurse_id', $this->id)
                              ->orWhere('student_id', $this->id);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    // Attribute accessors
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date_of_birth?->age,
        );
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->phone)) {
                    return 'Not provided';
                }
                return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $this->phone);
            },
        );
    }

    protected function academicInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->isStudent()) {
                    return null;
                }

                $infoParts = [];
                if ($this->course) {
                    $infoParts[] = $this->course;
                }
                if ($this->year_level) {
                    $infoParts[] = $this->year_level;
                }
                if ($this->section) {
                    $infoParts[] = "Section {$this->section}";
                }
                if ($this->academic_year) {
                    $infoParts[] = "(AY {$this->academic_year}-" . ($this->academic_year + 1) . ")";
                }

                return implode(' - ', $infoParts);
            },
        );
    }

    protected function requiresYearLevelUpdate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->needsYearLevelUpdate(),
        );
    }

    protected function emergencyContacts(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->medicalRecord) {
                    return [];
                }

                $contacts = [];
                
                if ($this->medicalRecord->emergency_contact_name_1 && $this->medicalRecord->emergency_contact_number_1) {
                    $contacts['primary'] = [
                        'name' => $this->medicalRecord->emergency_contact_name_1,
                        'number' => $this->medicalRecord->emergency_contact_number_1,
                        'type' => 'primary'
                    ];
                }
                
                if ($this->medicalRecord->emergency_contact_name_2 && $this->medicalRecord->emergency_contact_number_2) {
                    $contacts['secondary'] = [
                        'name' => $this->medicalRecord->emergency_contact_name_2,
                        'number' => $this->medicalRecord->emergency_contact_number_2,
                        'type' => 'secondary'
                    ];
                }
                
                return $contacts;
            },
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->first_name && $this->last_name) {
                    return trim($this->first_name . ' ' . $this->last_name);
                }
                return $this->email;
            }
        );
    }

    protected function onlineStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_online) {
                    return 'online';
                }
                
                if ($this->last_seen_at) {
                    $minutesAgo = $this->last_seen_at->diffInMinutes(now());
                    if ($minutesAgo < 5) {
                        return 'recently';
                    }
                }
                
                return 'offline';
            }
        );
    }

    protected function unreadMessagesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getUnreadMessagesCount(),
        );
    }

    // Medical Record Methods
    public function requiresMedicalRecord(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function hasMedicalRecord(): bool
    {
        return $this->relationLoaded('medicalRecord') 
            ? !is_null($this->medicalRecord) 
            : $this->medicalRecord()->exists();
    }

    public function createMedicalRecord(): MedicalRecord
    {
        if ($this->hasMedicalRecord()) {
            return $this->medicalRecord;
        }

        return $this->medicalRecord()->create([
            'user_id' => $this->id,
            'created_by' => $this->id,
            'created_at' => now(),
            'is_auto_created' => true, // ← ADD THIS LINE
            'updated_at' => now(),
        ]);
    }

    public function getMedicalRecordWithFallback(): MedicalRecord
    {
        if (!$this->hasMedicalRecord()) {
            return $this->createMedicalRecord();
        }

        return $this->medicalRecord;
    }

    public function ensureMedicalRecord(): ?MedicalRecord
    {
        if (!$this->requiresMedicalRecord()) {
            return null;
        }

        return $this->getMedicalRecordWithFallback();
    }

    public function canPerformHealthRelatedAction(): bool
    {
        return !$this->requiresMedicalRecord() || 
               ($this->hasMedicalRecord() && $this->hasCompleteMedicalRecord());
    }

    // Academic methods
    public function needsYearLevelUpdate(): bool
    {
        if (!$this->isStudent() || !$this->academic_year) {
            return false;
        }

        return $this->academic_year < $this->getCurrentAcademicYear();
    }

    public function getCurrentAcademicYear(): int
    {
        $now = Carbon::now();
        return ($now->month >= 8) ? $now->year : ($now->year - 1);
    }

    public function updateAcademicInfo(?string $yearLevel = null, ?string $section = null, ?string $course = null): bool
    {
        if (!$this->isStudent()) {
            return false;
        }

        $this->fill([
            'year_level' => $yearLevel ?? $this->year_level,
            'section' => $section ?? $this->section,
            'course' => $course ?? $this->course,
            'academic_year' => $this->getCurrentAcademicYear(),
            'year_level_updated_at' => now(),
        ]);

        return $this->save();
    }

    public function updatePersonalInfo(array $data): bool
    {
        $this->fill($data);
        return $this->save();
    }

    public function updateOnlineStatus($isOnline = true)
    {
        $this->update([
            'is_online' => $isOnline,
            'last_seen_at' => now(),
        ]);
    }

    // Medical Record Analysis Methods
    public function hasCompleteMedicalRecord(): bool
    {
        if (!$this->medicalRecord) {
            return false;
        }

        $requiredFields = [
            'blood_type', 'height', 'weight', 'allergies', 'past_illnesses',
            'emergency_contact_name_1', 'emergency_contact_number_1',
            'is_fully_vaccinated'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->medicalRecord->$field) && $this->medicalRecord->$field !== false) {
                return false;
            }
        }

        return true;
    }

    public function getMedicalRecordCompletion(): int
    {
        if (!$this->medicalRecord) {
            return 0;
        }

        $fields = [
            'blood_type', 'height', 'weight', 'allergies', 'past_illnesses',
            'emergency_contact_name_1', 'emergency_contact_number_1',
            'is_fully_vaccinated', 'vaccine_type'
        ];

        $completedFields = 0;
        foreach ($fields as $field) {
            if (!empty($this->medicalRecord->$field) || 
                ($this->medicalRecord->$field === false && in_array($field, ['is_fully_vaccinated']))) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($fields)) * 100);
    }

    public function getHealthRiskLevel(): string
    {
        if (!$this->medicalRecord) {
            return 'Unknown';
        }

        $riskFactors = 0;
        
        if ($this->medicalRecord->is_pwd) $riskFactors++;
        if (!empty($this->medicalRecord->allergies)) $riskFactors++;
        if ($this->medicalRecord->is_taking_maintenance_drugs) $riskFactors++;
        if ($this->medicalRecord->has_been_hospitalized_6_months) $riskFactors++;
        if (!empty($this->medicalRecord->past_illnesses)) $riskFactors++;
        if ($this->medicalRecord->has_undergone_surgery) $riskFactors++;
        
        if ($riskFactors >= 3) return 'High';
        if ($riskFactors >= 1) return 'Medium';
        return 'Low';
    }

    public function medicalRecordNeedsReview(): bool
    {
        if (!$this->medicalRecord || !$this->medicalRecord->updated_at) {
            return false;
        }
        
        return $this->medicalRecord->updated_at->diffInMonths(now()) > 6;
    }

    // Appointment and Health Methods
    public function getUpcomingAppointments()
    {
        return $this->appointments()
                   ->where('appointment_date', '>=', now()->toDateString())
                   ->whereIn('status', ['pending', 'confirmed'])
                   ->orderBy('appointment_date')
                   ->orderBy('appointment_time')
                   ->limit(5)
                   ->get();
    }

    public function hasPendingAppointments(): bool
    {
        return $this->appointments()
                   ->where('status', 'pending')
                   ->exists();
    }

    public function hasActiveConsultation()
    {
        return $this->consultations()
            ->whereIn('status', ['registered', 'waiting', 'in_progress'])
            ->whereDate('created_at', today())
            ->exists();
    }

    public function getRecentSymptomLogs(int $limit = 5)
    {
        return $this->symptomLogs()
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // Emergency Contact Methods
    public function hasEmergencyContacts(): bool
    {
        return !empty($this->emergency_contacts);
    }

    public function getPrimaryEmergencyContact(): ?array
    {
        $contacts = $this->emergency_contacts;
        return $contacts['primary'] ?? null;
    }

    public function getSecondaryEmergencyContact(): ?array
    {
        $contacts = $this->emergency_contacts;
        return $contacts['secondary'] ?? null;
    }

    public function getFormattedEmergencyContacts(): array
    {
        $contacts = $this->emergency_contacts;
        $formatted = [];

        if (!empty($contacts['primary'])) {
            $formatted[] = [
                'label' => 'Primary Emergency Contact',
                'name' => $contacts['primary']['name'],
                'number' => $this->formatPhoneNumber($contacts['primary']['number']),
                'type' => 'primary'
            ];
        }

        if (!empty($contacts['secondary'])) {
            $formatted[] = [
                'label' => 'Secondary Emergency Contact',
                'name' => $contacts['secondary']['name'],
                'number' => $this->formatPhoneNumber($contacts['secondary']['number']),
                'type' => 'secondary'
            ];
        }

        return $formatted;
    }

    private function formatPhoneNumber(string $phone): string
    {
        if (empty($phone)) {
            return 'Not provided';
        }
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
            return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/', '+$1 ($2) $3-$4', $phone);
        }
        
        return $phone;
    }

    // Vaccination Methods
    public function getVaccinationStatus(): array
    {
        if (!$this->medicalRecord) {
            return [
                'is_vaccinated' => false,
                'vaccine_type' => null,
                'doses' => null,
                'has_booster' => false,
                'booster_count' => 0,
                'vaccine_date' => null
            ];
        }

        return [
            'is_vaccinated' => $this->medicalRecord->is_fully_vaccinated,
            'vaccine_type' => $this->medicalRecord->vaccine_type,
            'doses' => $this->medicalRecord->number_of_doses,
            'has_booster' => $this->medicalRecord->number_of_boosters > 0,
            'booster_count' => $this->medicalRecord->number_of_boosters,
            'vaccine_date' => $this->medicalRecord->vaccine_date
        ];
    }

    // Chat Methods
    /**
     * Get all conversations for this user
     */
    public function conversations()
    {
        if ($this->isNurse()) {
            return $this->nurseConversations();
        } elseif ($this->isStudent()) {
            return $this->studentConversations();
        }
        
        return collect();
    }

    /**
     * Get unread messages for this user
     */
    public function unreadMessages()
    {
        return ChatMessage::whereHas('conversation', function ($query) {
            $query->where('nurse_id', $this->id)
                  ->orWhere('student_id', $this->id);
        })
        ->where('sender_id', '!=', $this->id)
        ->where('is_read', false);
    }

    /**
     * Get total unread messages count
     */
    public function getUnreadMessagesCount(): int
    {
        return $this->unreadMessages()->count();
    }

    /**
     * Check if user can chat with another user
     */
    public function canChatWith(User $otherUser): bool
    {
        // Nurses can chat with students and vice versa
        return ($this->isNurse() && $otherUser->isStudent()) ||
               ($this->isStudent() && $otherUser->isNurse());
    }

    /**
     * Get conversation with another user
     */
    public function getConversationWith(User $otherUser): ?ChatConversation
    {
        if (!$this->canChatWith($otherUser)) {
            return null;
        }

        if ($this->isNurse()) {
            return ChatConversation::betweenUsers($this->id, $otherUser->id)->first();
        } else {
            return ChatConversation::betweenUsers($otherUser->id, $this->id)->first();
        }
    }

    /**
     * Create or get existing conversation with another user
     */
    public function getOrCreateConversationWith(User $otherUser): ?ChatConversation
    {
        if (!$this->canChatWith($otherUser)) {
            return null;
        }

        if ($this->isNurse()) {
            return ChatConversation::findOrCreateBetween($this->id, $otherUser->id);
        } else {
            return ChatConversation::findOrCreateBetween($otherUser->id, $this->id);
        }
    }

    /**
     * Get recent conversations with unread counts
     */
    public function getRecentConversations($limit = 10)
    {
        return $this->allConversations()
            ->with(['nurse', 'student', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('sender_id', '!=', $this->id)
                      ->where('is_read', false);
            }])
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user has unread messages in specific conversation
     */
    public function hasUnreadMessagesInConversation($conversationId): bool
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $this->id)
            ->where('is_read', false)
            ->exists();
    }

    /**
     * Mark all messages as read in a conversation
     */
    public function markConversationAsRead($conversationId): bool
    {
        $conversation = ChatConversation::find($conversationId);
        
        if (!$conversation || !$conversation->isParticipant($this->id)) {
            return false;
        }

        return $conversation->markMessagesAsRead($this->id);
    }

    /**
     * Send message to another user
     */
    public function sendMessageTo(User $recipient, string $message): ?ChatMessage
    {
        if (!$this->canChatWith($recipient)) {
            return null;
        }

        $conversation = $this->getOrCreateConversationWith($recipient);
        
        if (!$conversation) {
            return null;
        }

        return $conversation->addMessage($this->id, $message, $this->role);
    }

    // Static helper methods
    public static function getYearLevelOptions(): array
    {
        return [
            '1st year' => '1st Year',
            '2nd year' => '2nd Year',
            '3rd year' => '3rd Year',
            '4th year' => '4th Year',
        ];
    }

    public static function getCourseOptions(): array
    {
        return [
            'BSIT' => 'Bachelor of Science in Information Technology',
            'BSBA' => 'Bachelor of Science in Business Administration',
            'BSBA-MM' => 'BSBA Marketing Management',
            'BSBA-FM' => 'BSBA Finance Management',
            'BSED' => 'BSED Secondary Education',
            'BEED' => 'BEED Elementary Education',
        ];
    }

    public static function getDepartmentOptions(): array
    {
        return [
            self::DEPARTMENT_BSIT => 'Information Technology',
            self::DEPARTMENT_BSBA => 'Business Administration',
            self::DEPARTMENT_EDUC => 'Education',
        ];
    }

    public static function getRoleOptions(): array
    {
        return [
            self::ROLE_STUDENT => 'Student',
            self::ROLE_NURSE => 'Nurse',
            self::ROLE_DEAN => 'General Dean',
            self::ROLE_DEAN_BSIT => 'BSIT Dean',
            self::ROLE_DEAN_BSBA => 'BSBA Dean',
            self::ROLE_DEAN_EDUC => 'EDUC Dean',
        ];
    }

    public static function getGenderOptions(): array
    {
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
        ];
    }

    // Query Scopes
    public function scopeStudents($query)
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    public function scopeNurses($query)
    {
        return $query->where('role', self::ROLE_NURSE);
    }

    public function scopeDeans($query)
    {
        return $query->whereIn('role', [
            self::ROLE_DEAN,
            self::ROLE_DEAN_BSIT,
            self::ROLE_DEAN_BSBA,
            self::ROLE_DEAN_EDUC
        ]);
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', [
            self::ROLE_NURSE,
            self::ROLE_DEAN,
            self::ROLE_DEAN_BSIT,
            self::ROLE_DEAN_BSBA,
            self::ROLE_DEAN_EDUC
        ]);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByCourse($query, $courses)
    {
        if (is_array($courses)) {
            return $query->whereIn('course', $courses);
        }
        return $query->where('course', $courses);
    }

    public function scopeWithoutMedicalRecord($query)
    {
        return $query->doesntHave('medicalRecord');
    }

    public function scopeStudentsWithoutMedicalRecord($query)
    {
        return $query->students()->doesntHave('medicalRecord');
    }

    public function scopeAvailableForChat($query)
    {
        return $query->whereIn('role', [self::ROLE_NURSE, self::ROLE_STUDENT])
                    ->where('is_online', true);
    }

  /**
 * Model events to enforce medical record requirements for students.
 */
protected static function booted()
{
    // Handle new user creation
    static::created(function ($user) {
        if ($user->requiresMedicalRecord()) {
            $user->medicalRecord()->create([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'is_auto_created' => true, // Auto-created flag
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    // Handle role changes
    static::updated(function ($user) {
        // If user role changed to student and they don't have a medical record
        if ($user->wasChanged('role') && 
            $user->role === self::ROLE_STUDENT && 
            !$user->hasMedicalRecord()) {
            $user->medicalRecord()->create([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'is_auto_created' => true, // Auto-created flag
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });
}
} // ← This closes the User class