<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleMembers = [
            ['member_id' => 'ST-2024-001', 'name' => 'Kamal Perera', 'type' => 'student', 'grade' => '10-A', 'contact_number' => '0712345678'],
            ['member_id' => 'ST-2024-002', 'name' => 'Amara Silva', 'type' => 'student', 'grade' => '10-A', 'contact_number' => '0715432109'],
            ['member_id' => 'ST-2024-003', 'name' => 'Rohit Kumar', 'type' => 'student', 'grade' => '9-B', 'contact_number' => '0778901234'],
            ['member_id' => 'ST-2024-004', 'name' => 'Priya Sharma', 'type' => 'student', 'grade' => '11-C', 'contact_number' => '0712567890'],
            ['member_id' => 'ST-2024-005', 'name' => 'Dinesh Jayawardene', 'type' => 'student', 'grade' => '9-A', 'contact_number' => '0776543210'],
            ['member_id' => 'ST-2024-006', 'name' => 'Anushka De Silva', 'type' => 'student', 'grade' => '10-B', 'contact_number' => '0779876543'],
            ['member_id' => 'ST-2024-007', 'name' => 'Ravi Thakur', 'type' => 'student', 'grade' => '11-A', 'contact_number' => '0713456789'],
            ['member_id' => 'ST-2024-008', 'name' => 'Madhura Kumari', 'type' => 'student', 'grade' => '10-C', 'contact_number' => '0747654321'],
            ['member_id' => 'TR-2024-001', 'name' => 'Mrs. Sunethra Silva', 'type' => 'teacher', 'grade' => 'Senior', 'contact_number' => '0778765432'],
            ['member_id' => 'TR-2024-002', 'name' => 'Mr. Saman Kumara', 'type' => 'teacher', 'grade' => 'Senior', 'contact_number' => '0712345987'],
            ['member_id' => 'TR-2024-003', 'name' => 'Ms. Lakshmi Patel', 'type' => 'teacher', 'grade' => 'Senior', 'contact_number' => '0779123456'],
            ['member_id' => 'TR-2024-004', 'name' => 'Mr. Rajesh Mishra', 'type' => 'teacher', 'grade' => 'Junior', 'contact_number' => '0715678901'],
            ['member_id' => 'TR-2024-005', 'name' => 'Dr. Anjali Verma', 'type' => 'teacher', 'grade' => 'Senior', 'contact_number' => '0774567890'],
            ['member_id' => 'ST-2024-009', 'name' => 'Nishan Wickramasinghe', 'type' => 'student', 'grade' => '12-A', 'contact_number' => '0749876543'],
            ['member_id' => 'ST-2024-010', 'name' => 'Chaminda Fernando', 'type' => 'student', 'grade' => '9-C', 'contact_number' => '0712908765'],
            ['member_id' => 'ST-2024-011', 'name' => 'Rashida Ahmed', 'type' => 'student', 'grade' => '10-A', 'contact_number' => '0776789012'],
            ['member_id' => 'ST-2024-012', 'name' => 'Sanduni Bandara', 'type' => 'student', 'grade' => '11-B', 'contact_number' => '0713456000'],
            ['member_id' => 'ST-2024-013', 'name' => 'Harsha Gunawardene', 'type' => 'student', 'grade' => '10-B', 'contact_number' => '0745678000'],
            ['member_id' => 'ST-2024-014', 'name' => 'Tharini Jayasuriya', 'type' => 'student', 'grade' => '9-A', 'contact_number' => '0771234567'],
            ['member_id' => 'ST-2024-015', 'name' => 'Vihanga Seneviratne', 'type' => 'student', 'grade' => '11-C', 'contact_number' => '0778567890'],
            ['member_id' => 'ST-2024-016', 'name' => 'Sachini Weerasinghe', 'type' => 'student', 'grade' => '10-C', 'contact_number' => '0776543000'],
            ['member_id' => 'TR-2024-006', 'name' => 'Mr. Pradeep Kumar', 'type' => 'teacher', 'grade' => 'Senior', 'contact_number' => '0712365478'],
            ['member_id' => 'ST-2024-017', 'name' => 'Rayan Hussain', 'type' => 'student', 'grade' => '12-B', 'contact_number' => '0749876000'],
            ['member_id' => 'ST-2024-018', 'name' => 'Madusha De Cruse', 'type' => 'student', 'grade' => '9-B', 'contact_number' => '0774321098'],
        ];

        foreach ($sampleMembers as $memberData) {
            \App\Models\Member::create([
                'member_id' => $memberData['member_id'],
                'name' => $memberData['name'],
                'type' => $memberData['type'],
                'grade' => $memberData['grade'],
                'contact_number' => $memberData['contact_number'],
            ]);
        }
    }
}
