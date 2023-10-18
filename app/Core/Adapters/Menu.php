<?php

namespace App\Core\Adapters;
use App\Models\User;
use App\Models\SystemUserGroup;
use App\Models\SystemMenu;
use App\Models\SystemMenuMapping;
// use App\Core\Adapters\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
/**
 * Adapter class to make the Metronic core lib compatible with the Laravel functions
 *
 * Class Menu
 *
 * @package App\Core\Adapters
 */
class Menu extends \App\Core\Menu
{
    public function __construct($items, $path = 'index') {
        // default $items come from menu.php config file
        $items = array();
       
        // you can override the menu $items variable here
        // get the data from the database and pass it into $items below.
        // but the format must be the same as the menu.php config.
        $items =  array(
            array(
                'title'   => 'Beranda',
                'path'    => 'index',
                'classes' => array('item' => 'me-lg-1'),
                'role'    => ['Administrator'],
            ),
            array(
                'title'   => 'User',
                'path'    => 'user',
                'classes' => array('item' => 'me-lg-1'),
                'role'    => ['Administrator'],
            ),
            array(
                'title'      => 'Resources',
                'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'),
                'attributes' => array(
                    'data-kt-menu-trigger'   => "hover",
                    'data-kt-menu-placement' => "bottom-start",
                ),
                'sub'        => array(
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => array(
                        // Documentation
                        array(
                            'title' => 'Documentation',
                            'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/abstract/abs027.svg", "svg-icon-2"),
                            'path'  => 'documentation/getting-started/overview',
                        ),
    
                        // Changelog
                        array(
                            'title' => 'Changelog v'.theme()->getVersion(),
                            'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/general/gen005.svg", "svg-icon-2"),
                            'path'  => 'documentation/getting-started/changelog',
                        ),
                    ),
                ),
            ),
            array(
                'title'      => 'Account',
                'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'),
                'attributes' => array(
                    'data-kt-menu-trigger'   => "hover",
                    'data-kt-menu-placement' => "bottom-start",
                ),
                'sub'        => array(
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => array(
                        array(
                            'title'  => 'Overview',
                            'path'   => 'account/overview',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ),
                        array(
                            'title'  => 'Settings',
                            'path'   => 'account/settings',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ),
                        array(
                            'title'      => 'Account',
                            'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-right'),
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                            'attributes' => array(
                                'data-kt-menu-trigger'   => "hover",
                                'data-kt-menu-placement' => "right-start",
                            ),
                            'sub'        => array(
                                'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                                'items' => array(
                                    array(
                                        'title'  => 'Overview',
                                        'path'   => 'account/overview',
                                        'bullet' => '<span class="bullet bullet-dot"></span>',
                                    ),
                                    array(
                                        'title'  => 'Settings',
                                        'path'   => 'account/settings',
                                        'bullet' => '<span class="bullet bullet-dot"></span>',
                                    ),
                                    array(
                                        'title'      => 'Security',
                                        'path'       => '#',
                                        'bullet'     => '<span class="bullet bullet-dot"></span>',
                                        'attributes' => array(
                                            'link' => array(
                                                "title"             => "Coming soon",
                                                "data-bs-toggle"    => "tooltip",
                                                "data-bs-trigger"   => "hover",
                                                "data-bs-dismiss"   => "hover",
                                                "data-bs-placement" => "right",
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'title'      => 'System',
                'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'),
                'attributes' => array(
                    'data-kt-menu-trigger'   => "hover",
                    'data-kt-menu-placement' => "bottom-start",
                ),
                'sub'        => array(
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => array(
                        array(
                            'title'      => 'Settings',
                            'path'       => '#',
                            'bullet'     => '<span class="bullet bullet-dot"></span>',
                            'attributes' => array(
                                'link' => array(
                                    "title"             => "Coming soon",
                                    "data-bs-toggle"    => "tooltip",
                                    "data-bs-trigger"   => "hover",
                                    "data-bs-dismiss"   => "hover",
                                    "data-bs-placement" => "right",
                                ),
                            ),
                        ),
                        array(
                            'title'  => 'Audit Log',
                            'path'   => 'log/audit',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ),
                        array(
                            'title'  => 'System Log',
                            'path'   => 'log/system',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ),
                    ),
                ),
            ),
        );
        // $path   = "index";


        // $items  = array();
        // $menus  = SystemMenu::select('*')
        // ->where('parent', '#')
        // ->where('type', '!=', 'function')
        // ->get();
        // foreach($menus as $key => $val){
        //     if(strlen($val['id_menu']) == 1){
        //         //*ROLE MENU 1
        //         $role           = array();
        //         $menu_mapping   = SystemMenuMapping::select('system_user_group.user_group_name')
        //         ->join('system_user_group', 'system_user_group.user_group_level', 'system_menu_mapping.user_group_level')
        //         ->where('system_menu_mapping.id_menu', $val['id_menu'])
        //         ->get();
        //         foreach($menu_mapping as $role_key => $role_val){
        //             array_push($role, $role_val['user_group_name']);
        //         }

        //         //*MENU 2
        //         $sub            = array();
        //         $childmenus     = SystemMenu::select('*')
        //         ->where('parent', $val['id_menu'])
        //         ->where('type', '!=', 'function')
        //         ->get();
        //         foreach($childmenus as $keyy => $vall){
        //             //*ROLE MENU 2
        //             $rolesub        = array();
        //             $menu_mapping   = SystemMenuMapping::select('system_user_group.user_group_name')
        //             ->join('system_user_group', 'system_user_group.user_group_level', 'system_menu_mapping.user_group_level')
        //             ->where('system_menu_mapping.id_menu', $vall['id_menu'])
        //             ->get();
                    
        //             foreach($menu_mapping as $role_key => $role_val){
        //                 array_push($rolesub, $role_val['user_group_name']);
        //             }

        //             //*MENU 3
        //             $subsub             = array();
        //             $childchildmenus    = SystemMenu::select('*')
        //             ->where('parent', $vall['id_menu'])
        //             ->where('type', '!=', 'function')
        //             ->get();
        //             foreach($childchildmenus as $keyyy => $valll){
        //                 //*ROLE MENU 3
        //                 $rolesubsub     = array();
        //                 $menu_mapping   = SystemMenuMapping::select('system_user_group.user_group_name')
        //                 ->join('system_user_group', 'system_user_group.user_group_level', 'system_menu_mapping.user_group_level')
        //                 ->where('system_menu_mapping.id_menu', $valll['id_menu'])
        //                 ->get();
        //                 foreach($menu_mapping as $role_key => $role_val){
        //                     array_push($rolesubsub, $role_val['user_group_name']);
        //                 }

        //                 $new_sub = array(
        //                     'title'   => $valll['text'],
        //                     'bullet'  => '<span class="bullet bullet-dot"></span>',
        //                     'path'    => $valll['id'],
        //                     'classes' => array('item' => 'me-lg-1'),
        //                     'role'    => $rolesub,
        //                 );
        //                 array_push($subsub, $new_sub);
        //             }

        //             //*ASSIGN MENU 2
        //             if(count($subsub) == 0){
        //                 $new_sub = array(
        //                     'title'   => $vall['text'],
        //                     'bullet'  => '<span class="bullet bullet-dot"></span>',
        //                     'path'    => $vall['id'],
        //                     'classes' => array('item' => 'me-lg-1'),
        //                     'role'    => $rolesub,
        //                 );
        //             }else{
        //                 $new_sub = array(
        //                     'title'   => $vall['text'],
        //                     'bullet'  => '<span class="bullet bullet-dot"></span>',
        //                     'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-right'),
        //                     'attributes' => array(
        //                         'data-kt-menu-trigger'   => "hover",
        //                         'data-kt-menu-placement' => "right-start",
        //                     ),
        //                     'role'    => $rolesub,
        //                     'sub'     => array(
        //                         'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
        //                         'items' => $subsub,
        //                     ),
        //                 );
        //             }
        //             array_push($sub, $new_sub);
        //         }

        //         //*ASSIGN MENU 1
        //         if(count($sub) == 0){
        //             $menu = array(
        //                 'title'   => $val['text'],
        //                 'path'    => $val['id'],
        //                 'classes' => array('item' => 'me-lg-1'),
        //                 'role'    => $role,
        //             );
        //         }else{
        //             $menu = array(
        //                 'title'   => $val['text'],
        //                 'classes'    => array('item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'),
        //                 'attributes' => array(
        //                     'data-kt-menu-trigger'   => "hover",
        //                     'data-kt-menu-placement' => "bottom-start",
        //                 ),
        //                 'role'    => $role,
        //                 'sub'     => array(
        //                     'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
        //                     'items' => $sub,
        //                 ),
        //             );
        //         }
        //         array_push($items, $menu);
        //     }
        // } 
        // print_r($items);exit;

        parent::__construct($items, $path);
        return $this;
    }

    public function build()
    {
        ob_start();

        parent::build();

        return ob_get_clean();
    }

    /**
     * Filter menu item based on the user permission using Spatie plugin
     *
     * @param $array
     */
    public static function filterMenuPermissions(&$array)
    {
        if (!is_array($array)) {
            return;
        }
        
        $user       = auth()->user();
        // $usergroup  = SystemUserGroup::select('user_group_name')
        // ->where('user_group_id', $user['user_group_id'])
        // ->first();

        // $user->syncRoles([]);
        // $user->assignRole($usergroup['user_group_name']);

        // check if the spatie plugin functions exist
        if (!method_exists($user, 'hasAnyPermission') || !method_exists($user, 'hasAnyRole')) {
            return;
        }

        foreach ($array as $key => &$value) {
            if (isset($value['permission']) && !$user->hasAnyPermission((array) $value['permission'])) {
                unset($array[$key]);
            }

            if (isset($value['role']) && !$user->hasAnyRole((array) $value['role'])) {
                unset($array[$key]);
            }

            if (is_array($value)) {
                self::filterMenuPermissions($value);
            }
        }
    }
}
