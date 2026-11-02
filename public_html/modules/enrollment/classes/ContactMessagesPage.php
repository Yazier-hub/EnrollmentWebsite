<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ContactMessagesController.php';

/** Request handling and view data for contact-messages. */
final class ContactMessagesPage extends EnrollmentPage
{
    protected const VIEW = 'contact-messages';

    protected function loadData(): array
    {
        $ctrl = (new ContactMessagesController())->handle();

        $messages      = $ctrl->messages();
        $activeMessage = $ctrl->activeMessage();
        $filter        = $ctrl->filter();
        $search        = $ctrl->search();
        $counts        = $ctrl->counts();
        $unreadCount   = $ctrl->unreadCount();
        $flash         = $ctrl->flash();
        $replies       = $ctrl->replies();

        $currentDateTime = date('l, F d, Y h:i A');
        $pageTitle       = 'Contact Messages';

        return [
            'currentDateTime' => $currentDateTime ?? null,
            'unreadCount' => $unreadCount ?? null,
            'flash' => $flash ?? null,
            'counts' => $counts ?? null,
            'filter' => $filter ?? null,
            'ctrl' => $ctrl ?? null,
            'search' => $search ?? null,
            'messages' => $messages ?? null,
            'activeMessage' => $activeMessage ?? null,
            'replies' => $replies ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
