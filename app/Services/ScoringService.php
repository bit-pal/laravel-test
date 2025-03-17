<?php

namespace App\Services;

use App\Models\User;

class ScoringService
{
    public function calculateScore(User $user1, User $user2)
    {
        if ($this->isLPGPpair($user1, $user2)) {
            return $this->calculateLPGPScore($user1, $user2);
        }

        return $this->calculateInvestorCompanyScore($user1, $user2);
    }

    private function isLPGPpair(User $user1, User $user2)
    {
        return ($user1->user_type === 2 && $user2->user_type === 2) ||
               ($user1->user_type === 2 && $user2->user_type === 1) ||
               ($user1->user_type === 1 && $user2->user_type === 2);
    }

    private function calculateLPGPScore(User $user1, User $user2)
    {
        $score = 0;
        $data1 = $user1->getScoringData();
        $data2 = $user2->getScoringData();

        // Fund type scoring (X*6 + Y*2)
        $fundTypeScore = $this->calculateFundTypeScore($data1['fund_type'], $data2['fund_type']);
        $score += $fundTypeScore;

        // Fund manager scoring (Y*4)
        $fundManagerScore = $this->calculateFundManagerScore($data1['fund_manager'], $data2['fund_manager']);
        $score += $fundManagerScore;

        // Geo scoring (X*4 + Y*2)
        $geoScore = $this->calculateGeoScore($data1['geo_preferences'], $data2['geo_preferences']);
        $score += $geoScore;

        // Sectors scoring (X*3 + Y*1)
        $sectorScore = $this->calculateSectorScore($data1['sector_preferences'], $data2['sector_preferences']);
        $score += $sectorScore;

        return $score;
    }

    private function calculateInvestorCompanyScore(User $user1, User $user2)
    {
        $score = 0;
        $data1 = $user1->getScoringData();
        $data2 = $user2->getScoringData();

        // Company stage/type scoring (X*6 + Y*2)
        $stageScore = $this->calculateStageScore($data1['company_stage_preferences'], $data2['company_stage_preferences']);
        $score += $stageScore;

        // Geo scoring (X*4 + Y*2)
        $geoScore = $this->calculateGeoScore($data1['geo_preferences'], $data2['geo_preferences']);
        $score += $geoScore;

        // Sectors scoring (X*3 + Y*1)
        $sectorScore = $this->calculateSectorScore($data1['sector_preferences'], $data2['sector_preferences']);
        $score += $sectorScore;

        return $score;
    }

    private function calculateFundTypeScore($fundType1, $fundType2)
    {
        if ($fundType1 === $fundType2) return 0;
        
        if (empty($fundType1)) return 6; // X case
        if (empty($fundType2)) return 2; // Y case
        
        return 8; // Both mismatch
    }

    private function calculateFundManagerScore($manager1, $manager2)
    {
        if ($manager1 === $manager2) return 0;
        return 4; // Y case only
    }

    private function calculateGeoScore($geo1, $geo2)
    {
        if (empty($geo1) && empty($geo2)) return 0;
        
        if (empty($geo1)) return 4; // X case
        if (empty($geo2)) return 2; // Y case
        
        $mismatches = array_diff($geo1, $geo2);
        return count($mismatches) * 6; // Both mismatch
    }

    private function calculateSectorScore($sectors1, $sectors2)
    {
        if (empty($sectors1) && empty($sectors2)) return 0;
        
        if (empty($sectors1)) return 3; // X case
        if (empty($sectors2)) return 1; // Y case
        
        $mismatches = array_diff($sectors1, $sectors2);
        return count($mismatches) * 4; // Both mismatch
    }

    private function calculateStageScore($stages1, $stages2)
    {
        if (empty($stages1) && empty($stages2)) return 0;
        
        if (empty($stages1)) return 6; // X case
        if (empty($stages2)) return 2; // Y case
        
        $mismatches = array_diff($stages1, $stages2);
        return count($mismatches) * 8; // Both mismatch
    }
} 