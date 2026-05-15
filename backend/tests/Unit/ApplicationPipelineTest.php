<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Applications\Services\ApplicationService;
use PHPUnit\Framework\TestCase;

class ApplicationPipelineTest extends TestCase
{
    public function testDocumentedPipelineStagesAreSupported(): void
    {
        $expected = [
            'applied',
            'matched',
            'screening',
            'assessment_invited',
            'assessment_completed',
            'shortlisted',
            'interview_scheduled',
            'interview_completed',
            'offer_pending',
            'offer_accepted',
            'placed',
            'rejected',
            'withdrawn',
        ];

        $this->assertSame($expected, ApplicationService::STAGES);
    }

    public function testTerminalStagesHaveNoTransitions(): void
    {
        $this->assertSame([], ApplicationService::TRANSITIONS['placed']);
        $this->assertSame([], ApplicationService::TRANSITIONS['rejected']);
        $this->assertSame([], ApplicationService::TRANSITIONS['withdrawn']);
    }
}
