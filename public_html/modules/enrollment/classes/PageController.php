<?php
/** Shared enrollment navigation. */
class PageController
{
    private $currentPage;

    public function __construct($page)
    {
        $this->currentPage = $page;
    }

    public function renderNav()
    {
        $navItems = [
            'dashboard-overview' => [
                'label' => 'Dashboard',
                'icon'  => 'fa-solid fa-chart-pie'
            ],
            'applications' => [
                'label' => 'Applications',
                'icon'  => 'fa-solid fa-file-pen'
            ],
            'enrollments' => [
                'label' => 'Enrollments',
                'icon'  => 'fa-solid fa-user-graduate'
            ],
            'students' => [
                'label' => 'Students',
                'icon'  => 'fa-solid fa-users'
            ],
            'courses' => [
                'label' => 'Courses',
                'icon'  => 'fa-solid fa-book'
            ],
            'sections' => [
                'label' => 'Sections',
                'icon'  => 'fa-solid fa-layer-group'
            ],
            'requirements' => [
                'label' => 'Requirements',
                'icon'  => 'fa-solid fa-list-check'
            ],
            'semesters' => [
                'label' => 'Semester Management',
                'icon'  => 'fa-solid fa-calendar-days'
            ],
            'archived-students' => [
                'label' => 'Archived Students',
                'icon'  => 'fa-solid fa-box-archive'
            ],
            'contact-messages' => [
                'label' => 'Contact Messages',
                'icon'  => 'fa-solid fa-envelope'
            ],
            'notifications' => [
                'label' => 'Notifications',
                'icon'  => 'fa-solid fa-bell'
            ]
        ];

        foreach ($navItems as $pageKey => $item) {
            $isActive    = ($this->currentPage === $pageKey);
            $activeClass = $isActive ? 'active-menu-link' : 'menu-link';

            echo '
            <li>
                <a href="?page=' . $pageKey . '" 
                   class="' . $activeClass . '" 
                   data-page="' . $pageKey . '">
                    <i class="' . $item['icon'] . '"></i>
                    <span>' . $item['label'] . '</span>
                    ' . ($isActive ? '<span class="active-indicator"></span>' : '') . '
                </a>
            </li>';
        }
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }
}
