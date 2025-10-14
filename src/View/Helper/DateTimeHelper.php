<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use DateTime;

/**
 * DateTime Helper
 * 
 * Provides utility functions for date and time calculations
 */
class DateTimeHelper extends Helper
{
    /**
     * Calculate age from date of birth
     *
     * @param mixed $dob Date of birth (can be CakePHP Date, DateTime, or string)
     * @return int Age in years
     */
    public function calculateAge($dob): int
    {
        if (!$dob) {
            return 0;
        }
        
        // Convert to DateTime object if it's not already
        if (is_string($dob)) {
            $dobDateTime = new DateTime($dob);
        } else {
            // Handle CakePHP Date/FrozenTime objects
            $dobDateTime = new DateTime($dob->format('Y-m-d'));
        }
        
        $now = new DateTime();
        return $dobDateTime->diff($now)->y;
    }
    
    /**
     * Format age display with proper grammar
     *
     * @param mixed $dob Date of birth
     * @return string Formatted age string
     */
    public function formatAge($dob): string
    {
        $age = $this->calculateAge($dob);
        
        if ($age === 0) {
            return 'Less than 1 year old';
        } elseif ($age === 1) {
            return '1 year old';
        } else {
            return "$age years old";
        }
    }
}