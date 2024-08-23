<?php

namespace Ajustatech\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use ReflectionClass;

class CommandHelperTest extends TestCase
{
    protected $commandHelper;
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();


        $this->commandHelper = new CommandHelper();

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->setProtectedProperty($this->commandHelper, "files", $this->filesystem);
    }

    public function test_load_config_from_array()
    {
        $config = [
            'vendor' => 'testVendor',
            'license' => 'MIT',
            'author' => [
                'name' => 'Test Author',
                'email' => 'author@example.com'
            ],
            'organization' => 'TestOrg',
            'ignore_subdir' => 'ignore'
        ];

        $this->commandHelper->loadConfigFromArray($config);
        $this->assertEquals($config, $this->getProtectedProperty($this->commandHelper, 'config'));
    }

    public function test_set_and_get_base_path()
    {
        $path = '/test/base/path';
        $this->commandHelper->setBasePath($path);
        $this->assertEquals($path, $this->commandHelper->getBasePath());
    }

    public function test_create_directory_structure()
    {
        $dirs = ['dir1', 'dir2', 'dir3'];
        $expects = ['/test/base/path/dir1', '/test/base/path/dir2', '/test/base/path/dir3'];

        $this->filesystem
            ->method('isDirectory')
            ->willReturn(false);

        $matcher = $this->exactly(count($dirs));

        $this->filesystem
            ->expects($matcher)
            ->method('makeDirectory')
            ->willReturnCallback(function ($directory, $mode, $recursive, $force) use ($expects, $matcher) {
                $count = $matcher->numberOfInvocations() - 1;
                $this->assertEquals($expects[$count], $directory);
                $this->assertEquals(0777, $mode);
                $this->assertTrue($recursive);
                $this->assertTrue($force);
            });

        $this->commandHelper->createDirectoryStructure('/test/base/path', $dirs);
    }

    public function test_create_file_from_stub()
    {
        $this->setProtectedProperty($this->commandHelper, "namespace", "TestOrg");
        $className = "TestClass";
        $basePath = "/test/base/path";
        $fileName = "testFile.php";
        $stubName = 'testStub';

        $stubContent = 'This is a stub file with $CLASS_NAME$ and $NAMESPACE$.';
        $expectedContent = 'This is a stub file with TestClass and TestOrg.';


        $this->filesystem->method('get')->willReturn($stubContent);
        $this->filesystem->method('exists')->willReturn(false);
        $this->filesystem->expects($this->once())
            ->method("put")
            ->with("{$basePath}/{$fileName}", $expectedContent);

        $method = $this->getProtectedMethod($this->commandHelper, 'createFileFromStub');
        $method->invokeArgs($this->commandHelper, [$basePath, $className, $stubName, $fileName]);
    }

    public function test_get_singular_class_name()
    {
        $this->assertEquals('Test', $this->commandHelper->getSingularClassName('Tests'));
    }

    public function test_get_class_name()
    {
        $this->assertEquals('TestClass', $this->commandHelper->getClassName('test-class'));
    }

    public function test_get_kebab_case_name()
    {
        $this->assertEquals('test-class', $this->commandHelper->getKebabCaseName('TestClass'));
    }

    public function test_get_snake_case_name()
    {
        $this->assertEquals('test_class', $this->commandHelper->getSnakeCaseName('TestClass'));
    }

    public function test_get_folder_name()
    {
        $this->commandHelper->loadConfigFromArray(['ignore_subdir' => 'ignore']);
        $this->assertEquals('last', $this->commandHelper->getFolderName('/some/path/to/last/ignore'));
    }

    public function test_get_namespace_from_path()
    {
        $this->commandHelper->loadConfigFromArray([
            'vendor' => 'testVendor',
            'license' => 'MIT',
            'author' => [
                'name' => 'Test Author',
                'email' => 'author@example.com'
            ],
            'organization' => 'TestOrg',
            'ignore_subdir' => 'src'
        ]);

        $this->commandHelper->setBasePath('home/vagrant/');
        $this->assertEquals('TestOrg\\Core', $this->commandHelper->getNamespaceFromPath('modules/Ajustatech/Core/src'));
    }

    public function test_add_contents(){
        $contents = ['key' => 'value'];
        $this->commandHelper->addContents($contents);
        $this->assertEquals($contents, $this->commandHelper->getContents());
    }

    public function test_create_multiple_files_from_stubs()
{
    $this->setProtectedProperty($this->commandHelper, "namespace", "TestOrg");
    $className = "TestClass";
    $basePath = "/test/base/path";

    // Definindo os stubs e os nomes dos arquivos esperados
    $stubs = [
        ['testStub1.stub' => "output/testFile1.php"],
        ['testStub1.stub' => "output/testFile2.php"],
        ['testStub3.stub' => "output/testFile3.php"],
    ];

    $stubContents = [
        'testStub1.stub' => 'Stub content for $CLASS_NAME$ in file 1.',
        'testStub3.stub' => 'Stub content for $CLASS_NAME$ in file 3.',
    ];

    $expectedContents = [
        "{$basePath}/output/testFile1.php" => 'Stub content for TestClass in file 1.',
        "{$basePath}/output/testFile2.php" => 'Stub content for TestClass in file 1.',
        "{$basePath}/output/testFile3.php" => 'Stub content for TestClass in file 3.',
    ];

    // Mockando o comportamento do Filesystem
    $this->filesystem->method('get')
        ->willReturnCallback(function ($stubPath) use ($stubContents) {
            return $stubContents[basename($stubPath)];
        });

    $this->filesystem->method('exists')->willReturn(false);

    $matcher = $this->exactly(count($stubs));

    $this->filesystem->expects($matcher)
        ->method('put')
        ->willReturnCallback(function ($filePath, $content) use ($expectedContents, $matcher) {
            $count = $matcher->numberOfInvocations() - 1;
            $this->assertEquals($expectedContents[$filePath], $content);
        });

    // Invocando o método createStubFiles com o novo formato de stubs
    $this->commandHelper->createStubFiles($basePath, $className, $stubs);
}


    // Métodos utilitários para acessar métodos e propriedades protegidos/privados
    protected function getProtectedProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    protected function getProtectedMethod($object, $methodName)
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public static function setProtectedProperty($object, $propertyName, $value)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
