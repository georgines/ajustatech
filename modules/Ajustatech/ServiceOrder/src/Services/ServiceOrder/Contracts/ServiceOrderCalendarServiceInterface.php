<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

interface ServiceOrderCalendarServiceInterface
{
    public function workingDays(): array;

    public function holidays(): array;
}
