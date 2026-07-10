<?php

use Illuminate\Support\Str;

function setNavItem($label, $url = null, $active = false): array
{
    return [
        'label' => $label,
        'url' => $url ?? '/' . Str::slug($label),
        'active' => $active,
    ];
}

return [
    'base' => [
        'args' => [
            'nav' => [
                setNavItem('Solutions', '/solutions', true),
                setNavItem('Evidence'),
                setNavItem('Insights'),
                setNavItem('Careers'),
                setNavItem('About'),
            ],
            'background' => 'light',
        ],
    ],
    'recirculation' => [
        'args' => [
            'items' => [setNavItem('Item 1'), setNavItem('Item 2'), setNavItem('Item 3'), setNavItem('Item 4')],
        ],
    ],
    'certif-bar' => [
        'args' => [
            'nav' => [setNavItem('CE', '/ce', true), setNavItem('FDA', '/fda', false)],
        ],
    ],
    'footer' => [
        'args' => [
            'nav' => [
                'about' => [
                    setNavItem('About the Press'),
                    setNavItem('Our authors'),
                    setNavItem('Awards'),
                    setNavItem('Support the Press'),
                    setNavItem('Harvard University website'),
                ],
                'contact' => [
                    setNavItem('Customer service'),
                    setNavItem('Sales representation and distribution'),
                    setNavItem('Subsidiary rights'),
                    setNavItem('Media inquiries'),
                ],
                'legals' => [setNavItem('Terms & Conditions'), setNavItem('Privacy Policy')],
            ],
        ],
    ],
];
