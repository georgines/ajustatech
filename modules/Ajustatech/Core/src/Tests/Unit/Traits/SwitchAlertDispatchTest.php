<?php

namespace Ajustatech\Core\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use Ajustatech\Core\Traits\SwitchAlertDispatch;
use PHPUnit\Framework\MockObject\MockObject;

class TestSwitchAlertDispatch
{
    use SwitchAlertDispatch;

    protected function dispatch(string $event, ...$params)
    {

    }
}

class SwitchAlertDispatchTest extends TestCase
{

    protected $mockTrait;

    protected function setUp(): void
    {
        $this->mockTrait = $this->getMockBuilder(TestSwitchAlertDispatch::class)
            ->onlyMethods(['dispatch'])
            ->getMock();
    }

    public function test_dispatchconfirmation_with_defaults()
    {
        $message = "Are you sure?";


        $expectedParams = [
            'type' => 'question',
            'message' => $message,
            'dispatchTo' => 'default',
            'parameters' => [],
            'confirmText' => 'Ok',
            'cancelText' => 'default',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->run();
    }

    public function test_dispatchconfirmation_with_custom_settings()
    {
        $message = "Delete this item?";

        $expectedParams = [
            'type' => 'warning',
            'message' => $message,
            'dispatchTo' => 'deleteItem',
            'parameters' => ['id' => 123, 'config' => 'default'],
            'confirmText' => 'Yes',
            'cancelText' => 'No',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->typeWarning()
            ->setButtonOK("Yes")
            ->setButtonCancel("No")
            ->dispatchConfirmation($message)
            ->to("deleteItem", id: 123, config: 'default')
            ->run();
    }

    public function test_typecuccess_changes_alertType()
    {
        $message = "Operation successful";

        $expectedParams = [
            'type' => 'success',
            'message' => $message,
            'dispatchTo' => 'default',
            'parameters' => [],
            'confirmText' => 'Ok',
            'cancelText' => 'default',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->typeSuccess()->run();
    }

    public function test_typeerror_changes_alertType()
    {
        $message = "An error occurred";

        $expectedParams = [
            'type' => 'error',
            'message' => $message,
            'dispatchTo' => 'default',
            'parameters' => [],
            'confirmText' => 'Ok',
            'cancelText' => 'default',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->typeError()->run();
    }

    public function test_setbuttonok_changes_confirmText()
    {
        $message = "Do you want to proceed?";

        $expectedParams = [
            'type' => 'question',
            'message' => $message,
            'dispatchTo' => 'default',
            'parameters' => [],
            'confirmText' => 'Proceed',
            'cancelText' => 'default',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->setButtonOK("Proceed")->run();
    }

    public function test_setbuttoncancel_changes_cancelText()
    {
        $message = "Do you want to abort?";

        $expectedParams = [
            'type' => 'question',
            'message' => $message,
            'dispatchTo' => 'default',
            'parameters' => [],
            'confirmText' => 'Ok',
            'cancelText' => 'Abort',
        ];

        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->setButtonCancel("Abort")->run();
    }

    public function test_to_changes_dispatchTo_and_parameters()
    {
        $message = "Update your profile";

        $expectedParams = [
            'type' => 'question',
            'message' => $message,
            'dispatchTo' => 'updateProfile',
            'parameters' => ['id' => 456, 'role' => 'admin'],
            'confirmText' => 'Ok',
            'cancelText' => 'default',
        ];


        $this->mockTrait->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('confirmation'),
                $this->equalTo($expectedParams)
            );

        $this->mockTrait->dispatchConfirmation($message)->to(dispatchTo: "updateProfile", id: 456, role: 'admin')->run();
    }
}
