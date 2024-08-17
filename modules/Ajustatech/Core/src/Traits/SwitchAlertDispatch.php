<?php

namespace Ajustatech\Core\Traits;

trait SwitchAlertDispatch
{
    protected $confirmText = 'Sim';
    protected $cancelText = 'Não';

    public function dispatchAlertSuccess($message)
    {
        $this->dispatchAlert('success', $message);
    }

    public function dispatchAlertError($message)
    {
        $this->dispatchAlert('error', $message);
    }

    public function dispatchAlertWarning($message)
    {
        $this->dispatchAlert('warning', $message);
    }

    public function dispatchAlertInfo($message)
    {
        $this->dispatchAlert('info', $message);
    }

    public function dispatchConfirm($message, $dispatchTo = null,  $confirmText = 'Sim', $cancelText = 'Não')
    {
        $this->dispatch('confirmation', confirmation: ['type' => 'question', 'message' => $message, 'dispatchTo' => $dispatchTo, 'confirmText' => $confirmText, 'cancelText' => $cancelText]);
    }

    public function dispatchConfirmationEvent($message, $dispatchTo, ...$parameters)
    {
        $this->dispatch('confirmation', confirmation: ['type' => 'question', 'message' => $message, 'dispatchTo' => $dispatchTo, 'parameters' => $parameters, 'confirmText' => $this->confirmText, 'cancelText' => $this->cancelText]);
    }

    public function setTextConfirmation(string $confirmText)
    {
        $this->confirmText = $confirmText;
    }

    public function setCancelText(string $cancelText)
    {
        $this->cancelText = $cancelText;
    }

    private function dispatchAlert($type, $message)
    {
        switch ($type) {
            case 'success':
                $this->dispatch('alert', alert: ['type' => 'success', 'message' => $message]);
                break;
            case 'error':
                $this->dispatch('alert', alert: ['type' => 'error', 'message' => $message]);
                break;
            case 'warning':
                $this->dispatch('alert', alert: ['type' => 'warning', 'message' => $message]);
                break;
            case 'info':
                $this->dispatch('alert', alert: ['type' => 'info', 'message' => $message]);
                break;
            default:
                $this->dispatch('alert', alert: ['type' => 'info', 'message' => $message]);
                break;
        }
    }
}
