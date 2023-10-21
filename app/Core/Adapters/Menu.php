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
