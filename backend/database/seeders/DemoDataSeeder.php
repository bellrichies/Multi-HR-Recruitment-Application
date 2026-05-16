<?php

declare(strict_types=1);

return function (PDO $db): void {
    $users = $db->query(
        'SELECT users.id, users.email, users.first_name, roles.slug role
         FROM users
         INNER JOIN user_roles ON user_roles.user_id = users.id
         INNER JOIN roles ON roles.id = user_roles.role_id
         ORDER BY users.id'
    )->fetchAll(PDO::FETCH_ASSOC);

    $byRole = [];

    foreach ($users as $user) {
        $byRole[$user['role']][] = $user;
    }

    $admin = $byRole['super_admin'][0] ?? null;
    $recruiters = $db->query('SELECT * FROM recruiter_profiles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $jobSeekers = $db->query('SELECT * FROM job_seeker_profiles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    if ($admin === null || $recruiters === [] || $jobSeekers === []) {
        echo 'DemoDataSeeder skipped: requires at least one admin, recruiter profile, and job seeker profile.' . PHP_EOL;
        return;
    }

    $jobSeeker = $jobSeekers[0];
    $recruiterUsers = [];

    foreach ($recruiters as $profile) {
        $recruiterUsers[(int) $profile['id']] = (int) $profile['user_id'];
    }

    seedDemoJobs($db, $recruiters, (int) $admin['id']);
    seedDemoNotifications($db, $users);
    seedDemoMessages($db, $recruiters, $recruiterUsers, (int) $jobSeeker['user_id']);
};

function seedDemoJobs(PDO $db, array $recruiters, int $adminUserId): void
{
    $jobs = [
        [
            'title' => 'Front Desk Officer',
            'slug' => 'demo-front-desk-officer',
            'description' => 'Welcome visitors, manage appointments, and support office administration for a growing services company.',
            'requirements' => 'OND or HND, clear communication, basic Microsoft Office skills, and professional presentation.',
            'responsibilities' => 'Handle front desk operations, receive calls, coordinate visitor logs, and support HR documentation.',
            'location' => 'Lagos',
            'employment_type' => 'full_time',
            'work_mode' => 'onsite',
            'salary_min' => 90000,
            'salary_max' => 130000,
            'experience_level' => 'entry',
            'skills' => ['Customer Service', 'Microsoft Office', 'Communication'],
        ],
        [
            'title' => 'Payroll and HR Assistant',
            'slug' => 'demo-payroll-hr-assistant',
            'description' => 'Support payroll preparation, employee records, onboarding documentation, and monthly HR reporting.',
            'requirements' => 'HND or BSc in Business Administration, Accounting, or related field with strong spreadsheet skills.',
            'responsibilities' => 'Maintain HR records, reconcile attendance, prepare payroll schedules, and support employee queries.',
            'location' => 'Abuja',
            'employment_type' => 'full_time',
            'work_mode' => 'hybrid',
            'salary_min' => 140000,
            'salary_max' => 220000,
            'experience_level' => 'mid',
            'skills' => ['Payroll', 'Excel', 'HR Administration'],
        ],
        [
            'title' => 'Sales Support Executive',
            'slug' => 'demo-sales-support-executive',
            'description' => 'Coordinate sales leads, prepare quotes, update CRM records, and support customer follow-up.',
            'requirements' => 'Strong phone etiquette, CRM familiarity, and ability to work with weekly sales targets.',
            'responsibilities' => 'Track leads, prepare reports, support customer onboarding, and coordinate handoff to account managers.',
            'location' => 'Port Harcourt',
            'employment_type' => 'contract',
            'work_mode' => 'remote',
            'salary_min' => 120000,
            'salary_max' => 180000,
            'experience_level' => 'entry',
            'skills' => ['CRM', 'Sales', 'Reporting'],
        ],
        [
            'title' => 'Operations Supervisor',
            'slug' => 'demo-operations-supervisor',
            'description' => 'Lead daily operations, supervise field staff, monitor service delivery, and escalate operational risks.',
            'requirements' => 'Three or more years of operations experience with staff supervision and reporting exposure.',
            'responsibilities' => 'Plan shifts, monitor KPIs, coordinate logistics, resolve escalations, and submit weekly performance reports.',
            'location' => 'Ibadan',
            'employment_type' => 'full_time',
            'work_mode' => 'onsite',
            'salary_min' => 200000,
            'salary_max' => 300000,
            'experience_level' => 'senior',
            'skills' => ['Operations', 'Team Leadership', 'Logistics'],
        ],
        [
            'title' => 'Customer Success Associate',
            'slug' => 'demo-customer-success-associate',
            'description' => 'Support client onboarding, resolve service issues, and monitor satisfaction for active customer accounts.',
            'requirements' => 'Experience in customer support, account coordination, or service operations with good written communication.',
            'responsibilities' => 'Run onboarding calls, document issues, coordinate resolutions, and prepare customer health updates.',
            'location' => 'Lagos',
            'employment_type' => 'full_time',
            'work_mode' => 'hybrid',
            'salary_min' => 150000,
            'salary_max' => 230000,
            'experience_level' => 'mid',
            'skills' => ['Customer Success', 'Account Management', 'Communication'],
        ],
    ];

    $insertJob = $db->prepare(
        'INSERT INTO jobs
         (uuid, recruiter_id, created_by, title, slug, description, requirements, responsibilities, location,
          employment_type, work_mode, salary_min, salary_max, currency, experience_level, application_deadline,
          status, published_at, created_at, updated_at)
         VALUES
         (:uuid, :recruiter_id, :created_by, :title, :slug, :description, :requirements, :responsibilities, :location,
          :employment_type, :work_mode, :salary_min, :salary_max, "NGN", :experience_level, :application_deadline,
          "open", NOW(), NOW(), NOW())'
    );
    $insertSkill = $db->prepare(
        'INSERT INTO job_skills (job_id, skill_name, required_level, created_at)
         VALUES (:job_id, :skill_name, "working", NOW())'
    );
    $exists = $db->prepare('SELECT id FROM jobs WHERE slug = :slug LIMIT 1');

    foreach ($jobs as $index => $job) {
        $exists->execute(['slug' => $job['slug']]);

        if ($exists->fetchColumn()) {
            continue;
        }

        $recruiter = $recruiters[$index % count($recruiters)];
        $insertJob->execute([
            'uuid' => uuid_create_local(),
            'recruiter_id' => (int) $recruiter['id'],
            'created_by' => $adminUserId,
            'title' => $job['title'],
            'slug' => $job['slug'],
            'description' => $job['description'],
            'requirements' => $job['requirements'],
            'responsibilities' => $job['responsibilities'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'work_mode' => $job['work_mode'],
            'salary_min' => $job['salary_min'],
            'salary_max' => $job['salary_max'],
            'experience_level' => $job['experience_level'],
            'application_deadline' => date('Y-m-d', strtotime('+45 days')),
        ]);
        $jobId = (int) $db->lastInsertId();

        foreach ($job['skills'] as $skill) {
            $insertSkill->execute(['job_id' => $jobId, 'skill_name' => $skill]);
        }
    }
}

function seedDemoNotifications(PDO $db, array $users): void
{
    $insert = $db->prepare(
        'INSERT INTO notifications (user_id, title, body, type, channel, data_json, read_at, created_at, updated_at)
         VALUES (:user_id, :title, :body, :type, "in_app", :data_json, :read_at, NOW(), NOW())'
    );
    $exists = $db->prepare(
        'SELECT id FROM notifications
         WHERE user_id = :user_id AND type = :type AND JSON_UNQUOTE(JSON_EXTRACT(data_json, "$.demo_key")) = :demo_key
         LIMIT 1'
    );

    $templates = [
        ['Account activity', 'Your demo dashboard has new activity ready for review.', 'account_activity'],
        ['New job match', 'A new job recommendation has been added to your workspace.', 'new_job_match'],
        ['Interview scheduled', 'A sample interview notification is available for testing.', 'interview_scheduled'],
        ['Wallet update', 'A sample wallet activity alert is ready for review.', 'wallet_funded'],
    ];

    foreach ($users as $user) {
        foreach ($templates as $index => [$title, $body, $type]) {
            $demoKey = 'demo-' . $type . '-' . $user['id'];
            $exists->execute(['user_id' => (int) $user['id'], 'type' => $type, 'demo_key' => $demoKey]);

            if ($exists->fetchColumn()) {
                continue;
            }

            $insert->execute([
                'user_id' => (int) $user['id'],
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data_json' => json_encode(['demo_key' => $demoKey, 'role' => $user['role']], JSON_THROW_ON_ERROR),
                'read_at' => $index === 0 ? date('Y-m-d H:i:s') : null,
            ]);
        }
    }
}

function seedDemoMessages(PDO $db, array $recruiters, array $recruiterUsers, int $jobSeekerUserId): void
{
    $adminId = (int) $db->query('SELECT users.id FROM users INNER JOIN user_roles ON user_roles.user_id = users.id INNER JOIN roles ON roles.id = user_roles.role_id WHERE roles.slug = "super_admin" ORDER BY users.id LIMIT 1')->fetchColumn();
    $job = $db->query('SELECT id FROM jobs ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $jobId = $job === false ? null : (int) $job['id'];
    $exists = $db->prepare('SELECT id FROM conversations WHERE subject = :subject LIMIT 1');
    $insertConversation = $db->prepare(
        'INSERT INTO conversations (conversation_type, subject, job_id, created_by, created_at, updated_at)
         VALUES (:conversation_type, :subject, :job_id, :created_by, NOW(), NOW())'
    );
    $insertParticipant = $db->prepare(
        'INSERT IGNORE INTO conversation_participants (conversation_id, user_id, last_read_at, created_at)
         VALUES (:conversation_id, :user_id, :last_read_at, NOW())'
    );
    $insertMessage = $db->prepare(
        'INSERT INTO messages (conversation_id, sender_id, message_body, created_at, updated_at)
         VALUES (:conversation_id, :sender_id, :message_body, NOW(), NOW())'
    );

    foreach ($recruiters as $index => $profile) {
        $recruiterUserId = $recruiterUsers[(int) $profile['id']] ?? null;

        if ($recruiterUserId === null) {
            continue;
        }

        $subject = 'Demo hiring conversation - recruiter ' . $profile['id'];
        $exists->execute(['subject' => $subject]);

        if ($exists->fetchColumn()) {
            continue;
        }

        $insertConversation->execute([
            'conversation_type' => 'recruiter_candidate',
            'subject' => $subject,
            'job_id' => $jobId,
            'created_by' => $recruiterUserId,
        ]);
        $conversationId = (int) $db->lastInsertId();

        foreach (array_filter([$recruiterUserId, $jobSeekerUserId, $adminId]) as $participantId) {
            $insertParticipant->execute([
                'conversation_id' => $conversationId,
                'user_id' => (int) $participantId,
                'last_read_at' => (int) $participantId === $jobSeekerUserId ? null : date('Y-m-d H:i:s'),
            ]);
        }

        $messages = [
            [$recruiterUserId, 'Hello, we reviewed your profile and would like to discuss a matching role.'],
            [$jobSeekerUserId, 'Thank you. I am available for screening this week.'],
            [$recruiterUserId, 'Great. The HR team will coordinate next steps and share interview details.'],
        ];

        foreach ($messages as [$senderId, $body]) {
            $insertMessage->execute([
                'conversation_id' => $conversationId,
                'sender_id' => (int) $senderId,
                'message_body' => $body,
            ]);
        }
    }
}
