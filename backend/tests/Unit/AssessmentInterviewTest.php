<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Assessments\Resources\AssessmentQuestionResource;
use PHPUnit\Framework\TestCase;

class AssessmentInterviewTest extends TestCase
{
    public function testPhaseSevenPermissionsAreConfigured(): void
    {
        $permissions = config('permissions.permissions', []);

        foreach ([
            'assessments.view',
            'assessments.create',
            'assessments.update',
            'assessments.assign',
            'assessments.take',
            'assessments.grade',
            'interviews.view',
            'interviews.schedule',
            'interviews.reschedule',
            'interviews.cancel',
            'interviews.feedback',
        ] as $permission) {
            $this->assertContains($permission, $permissions);
        }
    }

    public function testQuestionResourceHidesCorrectAnswerByDefault(): void
    {
        $resource = AssessmentQuestionResource::make([
            'id' => 1,
            'assessment_id' => 2,
            'question_text' => 'Pick one',
            'question_type' => 'multiple_choice',
            'options_json' => json_encode(['A', 'B']),
            'correct_answer_json' => json_encode('A'),
            'score' => 5,
            'created_at' => '2026-05-15 00:00:00',
        ]);

        $this->assertArrayNotHasKey('correct_answer', $resource);
    }
}
