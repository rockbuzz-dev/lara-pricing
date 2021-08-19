<?php

namespace Rockbuzz\LaraPricing\DTOs;

use \DateTime;

class ChangePlanOptions
{
    private $subscriptionName;

    private $dueDay;

    private $startAt;

    private $finishAt;

    public function __construct(
        string $subscriptionName,
        int $dueDay,
        DateTime $startAt,
        DateTime $finishAt = null
    ) {
        $this->subscriptionName = $subscriptionName;
        $this->dueDay = $dueDay;
        $this->startAt = $startAt;
        $this->finishAt = $finishAt;
    }

    /**
     * Get the value of subscriptionName
     */
    public function getSubscriptionName()
    {
        return $this->subscriptionName;
    }

    /**
     * Get the value of dueDay
     */
    public function getDueDay()
    {
        return $this->dueDay;
    }

    /**
     * Get the value of startAt
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Get the value of finishAt
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }
}
