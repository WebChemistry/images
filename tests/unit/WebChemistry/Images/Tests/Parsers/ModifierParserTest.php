<?php
namespace WebChemistry\Images\Tests\Parsers;

use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Parsers\ParserException;
use WebChemistry\Testing\TUnitTest;

class ModifierParserTest extends \Codeception\Test\Unit {

	use TUnitTest;

	public function testBasic() {
		$values = ModifierParser::parse('modifier');
		$this->assertSame([
			'modifier' => []
		], $values);
	}

	public function testParameter() {
		$values = ModifierParser::parse('modifier:param');
		$this->assertSame([
			'modifier' => ['param']
		], $values);
	}

	public function testParameters() {
		$values = ModifierParser::parse('modifier:param,param1');
		$this->assertSame([
			'modifier' => ['param', 'param1']
		], $values);
	}

	public function testCommaError() {
		$this->assertThrownException(function () {
			ModifierParser::parse('modifier:param,,param1');
		}, ParserException::class);
	}

	public function testMultipleModifiers() {
		$values = ModifierParser::parse('modifier|modifier2');
		$this->assertSame([
			'modifier' => [],
			'modifier2' => [],
		], $values);
		$values = ModifierParser::parse('modifier:param|modifier2:param');
		$this->assertSame([
			'modifier' => ['param'],
			'modifier2' => ['param'],
		], $values);
	}

	public function testArray() {
		$values = ModifierParser::parse('modifier:[id: param]');
		$this->assertSame([
			'modifier' => [
				'id' => 'param',
			]
		], $values);

		$values = ModifierParser::parse('modifier:[id: param, id2: param]');
		$this->assertSame([
			'modifier' => [
				'id' => 'param',
				'id2' => 'param',
			]
		], $values);

		$values = ModifierParser::parse('modifier:[id: [id: param], id2: param]');
		$this->assertSame([
			'modifier' => [
				'id' => [
					'id' => 'param',
				],
				'id2' => 'param',
			]
		], $values);


		$values = ModifierParser::parse('modifier:[id: [id: param], id2: param]|modifier2');
		$this->assertSame([
			'modifier' => [
				'id' => [
					'id' => 'param',
				],
				'id2' => 'param',
			],
			'modifier2' => [],
		], $values);
	}

	public function testNull() {
		$values = ModifierParser::parse('modifier:param,null');

		$this->assertSame([
			'modifier' => [
				'param', null,
			]
		], $values);
	}

}
