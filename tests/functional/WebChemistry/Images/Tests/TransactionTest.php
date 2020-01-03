<?php
namespace WebChemistry\Images\Tests;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Test\CustomHashResolver;
use Test\InvalidTransferResource;
use Test\ObjectHelper;
use Test\TImageTest;
use WebChemistry\Images\Exceptions\TransactionClosedException;
use WebChemistry\Images\Exceptions\TransactionException;
use WebChemistry\Images\Filters\FilterArgs;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\Transfer\StringResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Transactions\Transaction;
use WebChemistry\Images\Transactions\TransactionComposite;
use WebChemistry\Testing\TUnitTest;

class TransactionTest extends \Codeception\Test\Unit {

	use TUnitTest;
	use TImageTest;

	/** @var LocalStorage */
	private $storage;

	protected function _before() {
		@mkdir(__DIR__ . '/output');
		$filterRegistry = ObjectHelper::createFilterRegistry();
		$filterRegistry->addFilter('resize', function (FilterArgs $args): void {
			$image = $args->getImage();
			$image->resize(5, 5, Image::EXACT);
		});
		$filterRegistry->addFilter('resize2', function (FilterArgs $args): void {
			$image = $args->getImage();
			$image->resize(5, 5);
		});
		$filterRegistry->addFilter('resizeVar', function (FilterArgs $args, $width, $height, $flags = 0): void {
			$image = $args->getImage();

			$image->resize($width, $height, $flags);
		});

		$this->storage = $storage = ObjectHelper::createStorage(__DIR__, 'output', $filterRegistry, [
			'*' => 'default/upload.gif',
		], new CustomHashResolver());
	}

	private function createMoveGif(): IFileResource {
		$resource = $this->createStringResource();
		$resource->setName('move.gif');
		$resource = $this->storage->save($resource);

		$this->assertFileExists($this->getUploadPath('move.gif'));

		return $resource;
	}

	private function getUploadPath($name = 'upload.gif', $namespace = 'original') {
		return __DIR__ . '/output/' . $namespace . '/' . $name;
	}

	protected function _after() {
		$this->services->fileSystem->removeDirRecursive(__DIR__ . '/output');
	}

	public function testSuccessTransaction() {
		$move = $this->createMoveGif();

		$transaction = new Transaction($this->storage);

		$transaction->save($this->createStringResource());
		$transaction->save($this->createUploadResource());
		$transaction->copy($move, new FileResource('copy.gif'));
		$transaction->move($move, new FileResource('move2.gif'));

		$this->assertFileNotExists($this->getUploadPath());
		$this->assertFileNotExists($this->getUploadPath('string.gif'));
		$this->assertFileNotExists($this->getUploadPath('copy.gif'));
		$this->assertFileNotExists($this->getUploadPath('move2.gif'));

		$transaction->commit();

		$this->assertFileExists($this->getUploadPath());
		$this->assertFileExists($this->getUploadPath('string.gif'));
		$this->assertFileExists($this->getUploadPath('copy.gif'));
		$this->assertFileExists($this->getUploadPath('move2.gif'));
		$this->assertFileNotExists($this->getUploadPath('move.gif'));

		$transaction->rollback();

		$this->assertFileNotExists($this->getUploadPath());
		$this->assertFileNotExists($this->getUploadPath('string.gif'));
		$this->assertFileNotExists($this->getUploadPath('copy.gif'));
		$this->assertFileNotExists($this->getUploadPath('move2.gif'));
		$this->assertFileExists($this->getUploadPath('move.gif'));
	}

	public function testTransactionFailed() {
		$this->assertThrownException(function () {
			$transaction = new Transaction($this->storage);

			$transaction->save($this->createStringResource());
			$transaction->save(new InvalidTransferResource('', 'test.jpg'));

			$transaction->commit();
		}, TransactionException::class);

		$this->assertFileNotExists($this->getUploadPath('string.gif'));
	}

	public function testPersist() {
		$transaction = new Transaction($this->storage);

		$promise = $transaction->save($this->createStringResource());
		$called = false;
		$promise->then(function () use (&$called) {
			$called = true;
		});

		$transaction->commit();
		$transaction->persist();

		$this->assertTrue($called);
	}

	public function testFailPersist() {
		$transaction = new Transaction($this->storage);

		$promise = $transaction->save(new InvalidTransferResource('', 'test.jpg'));
		$called = false;
		$promise->error(function () use (&$called) {
			$called = true;
		});

		try {
			$transaction->commit();
		} catch (TransactionException $exception) {
			$this->assertTrue($called);

			return;
		}

		$this->fail();
	}

	public function testTransactionCompositeFailed() {
		$this->assertThrownException(function () {
			$composite = new TransactionComposite();

			$transaction = new Transaction($this->storage);
			$transaction->save($this->createStringResource());
			$composite->addTransaction($transaction);


			$transaction = new Transaction($this->storage);
			$transaction->save(new InvalidTransferResource('', 'test.jpg'));

			$composite->addTransaction($transaction);

			$composite->commit();
		}, TransactionException::class);

		$this->assertFileNotExists($this->getUploadPath('string.gif'));
	}

	public function testTransactionCompositeAlreadyClosed() {
		$this->assertThrownException(function () {
			$composite = new TransactionComposite();

			$persist = new Transaction($this->storage);
			$persist->save($this->createStringResource());
			$composite->addTransaction($persist);

			$transaction = new Transaction($this->storage);
			$transaction->save(new InvalidTransferResource('', 'test.jpg'));

			$composite->addTransaction($transaction);

			$persist->commit();

			$composite->commit();
		}, TransactionClosedException::class);

		$this->assertFileNotExists($this->getUploadPath('string.gif'));
	}

	public function testTransactionCompositePersisted() {
		$this->assertThrownException(function () {
			$composite = new TransactionComposite();

			$persist = new Transaction($this->storage);
			$persist->save($this->createStringResource());
			$composite->addTransaction($persist);

			$transaction = new Transaction($this->storage);
			$transaction->save(new InvalidTransferResource('', 'test.jpg'));

			$composite->addTransaction($transaction);

			$persist->commit();
			$persist->persist();

			$composite->commit();
		}, TransactionClosedException::class);

		$this->assertFileExists($this->getUploadPath('string.gif'));
	}

}
