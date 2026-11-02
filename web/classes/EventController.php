<?php
// classes/EventController.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/EventType.php';
require_once __DIR__ . '/EventRepository.php';

class EventController
{
    public EventRepository $repository;

    public function __construct()
    {
        $this->repository = (new EventRepository())->load();
    }

    public function handleRequest(): void
    {
        // This page only reads the published event catalog.
    }

    public function viewData(): array
    {
        return [
            'eventsByMonth' => $this->repository->byMonth(),
            'totalEvents' => $this->repository->total(),
            'upcomingCount' => $this->repository->upcomingCount(),
            'ongoingCount' => $this->repository->ongoingCount(),
            'completedCount' => $this->repository->completedCount(),
            'fatalMessage' => null,
        ];
    }

}
