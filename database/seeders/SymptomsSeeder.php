<?php

namespace Database\Seeders;

use App\Models\Symptom;
use App\Models\PossibleIllness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SymptomsSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data to avoid duplicates - use correct table names
        DB::table('symptom_illness')->truncate();
        Symptom::truncate();
        PossibleIllness::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create symptoms
        $symptoms = [
            'Fever', 'Headache', 'Cough', 'Sore Throat', 'Runny Nose',
            'Body Aches', 'Fatigue', 'Nausea', 'Vomiting', 'Diarrhea',
            'Abdominal Pain', 'Chest Pain', 'Shortness of Breath',
            'Dizziness', 'Skin Rash', 'Loss of Appetite', 'Chills',
            'Sneezing', 'Congestion', 'Muscle Pain', 'Weakness',
            'Ear Pain', 'Swollen Glands', 'Red Eyes', 'Itchy Eyes',
            'Wheezing', 'Rapid Heartbeat', 'Fainting', 'Severe Bleeding',
            'Loss of Consciousness', 'Difficulty Swallowing', 'Blurred Vision'
        ];
        
        foreach ($symptoms as $symptomName) {
            Symptom::create(['name' => $symptomName]);
        }
        
        // Create possible illnesses (including general fallback conditions)
        $illnesses = [
            'Common Cold', 'Flu (Influenza)', 'COVID-19', 'Allergies',
            'Stomach Flu (Gastroenteritis)', 'Food Poisoning', 'Migraine',
            'Sinus Infection', 'Bronchitis', 'Pneumonia', 'Strep Throat',
            'Asthma Attack', 'Anxiety Attack', 'Dehydration', 'Ear Infection',
            'Conjunctivitis (Pink Eye)', 'Urinary Tract Infection', 'Mononucleosis',
            // General fallback conditions
            'Stress', 'Minor Viral Infection', 'General Malaise', 'Fatigue Syndrome',
            'Muscle Strain', 'Sleep Deprivation', 'Mild Dehydration'
        ];
        
        foreach ($illnesses as $illnessName) {
            PossibleIllness::create(['name' => $illnessName]);
        }
        
        // Link symptoms to illnesses
        $this->linkSymptomsToIllnesses();
    }
    
    private function linkSymptomsToIllnesses()
    {
        $relationships = [
            // Specific conditions
            'Common Cold' => ['Runny Nose', 'Sneezing', 'Sore Throat', 'Cough', 'Congestion'],
            'Flu (Influenza)' => ['Fever', 'Body Aches', 'Fatigue', 'Headache', 'Cough', 'Chills'],
            'COVID-19' => ['Fever', 'Cough', 'Shortness of Breath', 'Fatigue', 'Loss of Appetite'],
            'Allergies' => ['Sneezing', 'Runny Nose', 'Itchy Eyes', 'Congestion'],
            'Stomach Flu (Gastroenteritis)' => ['Nausea', 'Vomiting', 'Diarrhea', 'Abdominal Pain', 'Fever'],
            'Food Poisoning' => ['Nausea', 'Vomiting', 'Diarrhea', 'Abdominal Pain', 'Fever', 'Weakness'],
            'Migraine' => ['Headache', 'Nausea', 'Dizziness'],
            'Sinus Infection' => ['Congestion', 'Headache', 'Runny Nose'],
            'Bronchitis' => ['Cough', 'Shortness of Breath', 'Fatigue', 'Chest Pain'],
            'Pneumonia' => ['Fever', 'Cough', 'Shortness of Breath', 'Chest Pain', 'Fatigue'],
            'Strep Throat' => ['Sore Throat', 'Fever', 'Headache', 'Swollen Glands'],
            'Asthma Attack' => ['Shortness of Breath', 'Wheezing', 'Cough', 'Chest Pain'],
            'Anxiety Attack' => ['Rapid Heartbeat', 'Shortness of Breath', 'Dizziness', 'Chest Pain'],
            'Dehydration' => ['Dizziness', 'Weakness', 'Fatigue', 'Rapid Heartbeat'],
            'Ear Infection' => ['Ear Pain', 'Fever', 'Headache', 'Dizziness'],
            'Conjunctivitis (Pink Eye)' => ['Red Eyes', 'Itchy Eyes', 'Runny Nose'],
            'Urinary Tract Infection' => ['Abdominal Pain', 'Fever', 'Nausea'],
            'Mononucleosis' => ['Fatigue', 'Fever', 'Sore Throat', 'Swollen Glands'],
            
            // General fallback conditions - these catch most symptoms
            'Stress' => ['Headache', 'Body Aches', 'Fatigue', 'Dizziness', 'Muscle Pain', 'Weakness'],
            'Minor Viral Infection' => ['Fever', 'Fatigue', 'Body Aches', 'Headache', 'Weakness', 'Loss of Appetite'],
            'General Malaise' => ['Fatigue', 'Weakness', 'Body Aches', 'Headache', 'Loss of Appetite'],
            'Fatigue Syndrome' => ['Fatigue', 'Weakness', 'Muscle Pain', 'Headache', 'Dizziness'],
            'Muscle Strain' => ['Body Aches', 'Muscle Pain', 'Weakness'],
            'Sleep Deprivation' => ['Fatigue', 'Headache', 'Dizziness', 'Weakness'],
            'Mild Dehydration' => ['Dizziness', 'Headache', 'Fatigue', 'Weakness']
        ];
        
        foreach ($relationships as $illnessName => $symptomNames) {
            $illness = PossibleIllness::where('name', $illnessName)->first();
            if ($illness) {
                $symptomIds = Symptom::whereIn('name', $symptomNames)->pluck('id');
                $illness->symptoms()->attach($symptomIds);
            }
        }
    }
}