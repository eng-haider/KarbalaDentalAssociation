<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // WhatsApp Business templates, sent through Ad Scientist.
    'adscientist' => [
        'url' => env('ADSCIENTIST_URL', 'https://ai.adscientist.iq/v1'),
        'key' => env('ADSCIENTIST_API_KEY'),
        // A dashboard login for uploading invitation cards: Ad Scientist's
        // media upload fails for API keys, but works signed in as a user.
        'email' => env('ADSCIENTIST_EMAIL'),
        'password' => env('ADSCIENTIST_PASSWORD'),
        // «دعوة حضور»: the card as its image, then the guest's name and invitation link.
        'invitation_template' => env('ADSCIENTIST_INVITATION_TEMPLATE', 'oct_26'),
        // The same wording without the image, sent while Meta reviews the
        // one above (an edited template goes back to review).
        'invitation_fallback_template' => env('ADSCIENTIST_INVITATION_FALLBACK_TEMPLATE', 'osif_invitation_text'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
