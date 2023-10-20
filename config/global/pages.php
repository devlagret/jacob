<?php


//!ORIGINAL CODE, DIGANTI KARENA PADA CONFIG TIDAK BOLEH MEMANGGIL MODEL
// use App\Models\SystemMenu;

// $menu           = SystemMenu::select('*')->where('type', 'file')->get();
// $menu_array     = array();
// foreach($menu as $key => $val){
//     $menu_array[$val['id']] = array(
//         'title'       => $val['text'],
//         'description' => '',
//         'view'        => $val['id'],
//         'layout'      => array(
//             'page-title' => array(
//                 'description' => false,
//                 'breadcrumb'  => false,
//             ),
//         ),
//         'assets' => array(
//             'custom' => array(
//                 'css' => array(
//                     'plugins/custom/datatables/datatables.bundle.css',
//                 ),
//                 'js'  => array(
//                     'plugins/custom/datatables/datatables.bundle.js',
//                 ),
//             ),
//         ),
//     );
// }

$menu_array['login'] = array(
    'title'  => 'Login',
    'assets' => array(
        'custom' => array(
            'js' => array(
                'js/custom/authentication/sign-in/general.js',
            ),
        ),
    ),
    'layout' => array(
        'main' => array(
            'type' => 'blank', // Set blank layout
            'body' => array(
                'class' => theme()->isDarkMode() ? '' : 'bg-body',
            ),
        ),
    ),
);
$menu_array['register'] = array(
    'title'  => 'Register',
    'assets' => array(
        'custom' => array(
            'js' => array(
                'js/custom/authentication/sign-up/general.js',
            ),
        ),
    ),
    'layout' => array(
        'main' => array(
            'type' => 'blank', // Set blank layout
            'body' => array(
                'class' => theme()->isDarkMode() ? '' : 'bg-body',
            ),
        ),
    ),
);
$menu_array['forgot-password'] = array(
    'title'  => 'Forgot Password',
    'assets' => array(
        'custom' => array(
            'js' => array(
                'js/custom/authentication/password-reset/password-reset.js',
            ),
        ),
    ),
    'layout' => array(
        'main' => array(
            'type' => 'blank', // Set blank layout
            'body' => array(
                'class' => theme()->isDarkMode() ? '' : 'bg-body',
            ),
        ),
    ),
);
$menu_array['log'] = array(
    'audit'  => array(
        'title'  => 'Audit Log',
        'assets' => array(
            'custom' => array(
                'css' => array(
                    'plugins/custom/datatables/datatables.bundle.css',
                ),
                'js'  => array(
                    'plugins/custom/datatables/datatables.bundle.js',
                ),
            ),
        ),
    ),
    'system' => array(
        'title'  => 'System Log',
        'assets' => array(
            'custom' => array(
                'css' => array(
                    'plugins/custom/datatables/datatables.bundle.css',
                ),
                'js'  => array(
                    'plugins/custom/datatables/datatables.bundle.js',
                ),
            ),
        ),
    ),
);

return $menu_array;




//! NOT USED CODE----------------------------------------------------------------------------------------------
// return array(
//     'index' => a rray(
//         'title'       => 'Beranda',
//         'description' => '',
//         'view'        => 'index',
//         'layout'      => array(
//             'page-title' => array(
//                 'description' => true,
//                 'breadcrumb'  => false,
//             ),
//         ),
//         'assets'      => array(
//             'custom' => array(
//                 'js' => array(),
//             ),
//         ),
//     ),

//     'user' => array(
//         'title'       => 'User',
//         'description' => '',
//         'view'        => 'index',
//         'layout'      => array(
//             'page-title' => array(
//                 'description' => true,
//                 'breadcrumb'  => false,
//             ),
//         ),
//         'assets'      => array(
//             'custom' => array(
//                 'js' => array(),
//             ),
//         ),
//     ),

//     'login'           => array(
//         'title'  => 'Login',
//         'assets' => array(
//             'custom' => array(
//                 'js' => array(
//                     'js/custom/authentication/sign-in/general.js',
//                 ),
//             ),
//         ),
//         'layout' => array(
//             'main' => array(
//                 'type' => 'blank', // Set blank layout
//                 'body' => array(
//                     'class' => theme()->isDarkMode() ? '' : 'bg-body',
//                 ),
//             ),
//         ),
//     ),

//     'register'        => array(
//         'title'  => 'Register',
//         'assets' => array(
//             'custom' => array(
//                 'js' => array(
//                     'js/custom/authentication/sign-up/general.js',
//                 ),
//             ),
//         ),
//         'layout' => array(
//             'main' => array(
//                 'type' => 'blank', // Set blank layout
//                 'body' => array(
//                     'class' => theme()->isDarkMode() ? '' : 'bg-body',
//                 ),
//             ),
//         ),
//     ),

//     'forgot-password' => array(
//         'title'  => 'Forgot Password',
//         'assets' => array(
//             'custom' => array(
//                 'js' => array(
//                     'js/custom/authentication/password-reset/password-reset.js',
//                 ),
//             ),
//         ),
//         'layout' => array(
//             'main' => array(
//                 'type' => 'blank', // Set blank layout
//                 'body' => array(
//                     'class' => theme()->isDarkMode() ? '' : 'bg-body',
//                 ),
//             ),
//         ),
//     ),

//     'log' => array(
//         'audit'  => array(
//             'title'  => 'Audit Log',
//             'assets' => array(
//                 'custom' => array(
//                     'css' => array(
//                         'plugins/custom/datatables/datatables.bundle.css',
//                     ),
//                     'js'  => array(
//                         'plugins/custom/datatables/datatables.bundle.js',
//                     ),
//                 ),
//             ),
//         ),
//         'system' => array(
//             'title'  => 'System Log',
//             'assets' => array(
//                 'custom' => array(
//                     'css' => array(
//                         'plugins/custom/datatables/datatables.bundle.css',
//                     ),
//                     'js'  => array(
//                         'plugins/custom/datatables/datatables.bundle.js',
//                     ),
//                 ),
//             ),
//         ),
//     ),

//     'account' => array(
//         'overview' => array(
//             'title'  => 'Account Overview',
//             'view'   => 'account/overview/overview',
//             'assets' => array(
//                 'custom' => array(
//                     'js' => array(
//                         'js/custom/widgets.js',
//                     ),
//                 ),
//             ),
//         ),

//         'settings' => array(
//             'title'  => 'Account Settings',
//             'assets' => array(
//                 'custom' => array(
//                     'js' => array(
//                         'js/custom/account/settings/profile-details.js',
//                         'js/custom/account/settings/signin-methods.js',
//                         'js/custom/modals/two-factor-authentication.js',
//                     ),
//                 ),
//             ),
//         ),
//     ),

//     'users'         => array(
//         'title' => 'User List',

//         '*' => array(
//             'title' => 'Show User',

//             'edit' => array(
//                 'title' => 'Edit User',
//             ),
//         ),
//     ),

// );
