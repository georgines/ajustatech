<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    protected function displayMessage(string $message, string $textColor = 'default', ?string $bgColor = null)
    {

        $colorTag = "<fg={$textColor}>";
        if ($bgColor) {
            $colorTag = "<fg={$textColor};bg={$bgColor}>";
        }
        $formattedMessage = "{$colorTag}{$message}</>";
        $this->line($formattedMessage);
    }
}
