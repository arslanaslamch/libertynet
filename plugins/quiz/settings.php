<?php
return array(
    'site-other-settings' => array(
        'title' => 'Quiz',
        'description' => '',
        'settings' => array(
            'allow-members-create-quiz' => array(
                'type' => 'boolean',
                'title' => lang('quiz::allow-member-to-create-quiz'),
                'description' => lang('quiz::allow-member-to-create-quiz-desc'),
                'value' => 1,
            ),
            'quiz-list-limit' => array(
                'type' => 'text',
                'title' => 'Quiz Listing Per Page',
                'description' => 'Set your limit per page listing of quizes',
                'value' => 12
            ),
			'quiz-question-limit' => array(
                'type' => 'text',
                'title' => 'Number of Questions Per Quiz',
                'description' => 'Set the limit for number of questions for a quiz',
                'value' => 3
            )
        )
    )
);