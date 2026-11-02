<?php
require_once __DIR__ . '/PageView.php';
require_once __DIR__ . '/PageAssets.php';
require_once __DIR__ . '/SidebarView.php';

/** Loads data before any HTML, then renders the shared layout and page template. */
abstract class EnrollmentPage
{
    protected const VIEW = '';

    public const ROUTES = [
        'dashboard-overview' => DashboardOverviewPage::class,
        'applications' => ApplicationsPage::class,
        'application-details' => ApplicationDetailsPage::class,
        'application-edit' => ApplicationEditPage::class,
        'students' => StudentsPage::class,
        'student-details' => StudentDetailsPage::class,
        'student-edit' => StudentEditPage::class,
        'student-subjects' => StudentSubjectsPage::class,
        'enrollments' => EnrollmentsPage::class,
        'courses' => CoursesPage::class,
        'sections' => SectionsPage::class,
        'requirements' => RequirementsPage::class,
        'semesters' => SemestersPage::class,
        'archived-students' => ArchivedStudentsPage::class,
        'contact-messages' => ContactMessagesPage::class,
        'notifications' => NotificationsPage::class,
        'logout' => LogoutPage::class,
    ];

    abstract protected function loadData(): array;

    final public function render($pageController = null): void
    {
        $viewData = $this->loadData();
        $pageKey = static::VIEW;
        $pageTitle = $viewData['pageTitle'] ?? 'BCP Enrollment';
        $pageAssets = new PageAssets($pageKey);
        $layout = compact('pageTitle', 'pageAssets', 'pageController', 'pageKey');
        $sidebar = (new SidebarView())->data();

        self::renderTemplate('includes/header.php', $layout);
        self::renderTemplate('includes/sidebar.php', array_merge($layout, $sidebar));
        self::renderTemplate('pages/' . $pageKey . '.php', $viewData);
        self::renderTemplate('includes/footer.php', $layout);
    }

    private static function renderTemplate(string $template, array $variables): void
    {
        extract($variables, EXTR_SKIP);
        require __DIR__ . '/../' . $template;
    }

    public static function forRoute(string $route): self
    {
        if (!isset(self::ROUTES[$route])) {
            throw new InvalidArgumentException('Unknown enrollment page.');
        }
        $class = self::ROUTES[$route];
        require_once __DIR__ . '/' . $class . '.php';
        return new $class();
    }
}
