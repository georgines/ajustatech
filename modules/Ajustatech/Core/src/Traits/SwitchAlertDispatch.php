<?php

namespace Ajustatech\Core\Traits;

trait SwitchAlertDispatch
{
    protected string $alertType = 'question';
    protected string $confirmText = "Ok";
    protected string $cancelText = "default";
    protected string $dispatchTo = "default";
    protected array $parameters = [];
    protected string $message = "";

    public function dispatchConfirmation(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function typeSuccess()
    {
        $this->alertType = 'success';
        return $this;
    }

    public function typeError()
    {
        $this->alertType = 'error';
        return $this;
    }

    public function typeWarning()
    {
        $this->alertType = 'warning';
        return $this;
    }

    public function typeInfo()
    {
        $this->alertType = 'info';
        return $this;
    }

    public function setButtonOK(string $confirmText = "Ok")
    {
        $this->confirmText = $confirmText;
        return $this;
    }

    public function to(string $dispatchTo, ...$parameters)
    {
        $this->dispatchTo = $dispatchTo;
        $this->parameters = $parameters;
        return $this;
    }

    public function setButtonCancel(string $cancelText = "Cancel")
    {
        $this->cancelText = $cancelText;
        return $this;
    }

    public function run()
    {
        $this->dispatch(
            'confirmation',
            [
                'type' => $this->alertType,
                'message' => $this->message,
                'dispatchTo' => $this->dispatchTo,
                'parameters' => $this->parameters,
                'confirmText' => $this->confirmText,
                'cancelText' => $this->cancelText,
            ]
        );
    }
}
