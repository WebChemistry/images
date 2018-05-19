<?php
namespace WebChemistry\Images\Tests\Parsers;

use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Parsers\ParserException;
use WebChemistry\Images\Parsers\Variable;
use WebChemistry\Testing\TUnitTest;

class ModifierParserTest extends \Codeception\Test\Unit {

	use TUnitTest;

	public function testBasic() {
		$values = ModifierParser::parse('modifier')->getValues();
		$this->assertSame([
			'modifier' => []
		], $values);
	}

	public function testParameter() {
		$values = ModifierParser::parse('modifier:param')->getValues();
		$this->assertSame([
			'modifier' => ['param']
		], $values);
	}

	public function testParameters() {
		$values = ModifierParser::parse('modifier:param,param1')->getValues();
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
		$values = ModifierParser::parse('modifier|modifier2')->getValues();
		$this->assertSame([
			'modifier' => [],
			'modifier2' => [],
		], $values);
		$values = ModifierParser::parse('modifier:param|modifier2:param')->getValues();
		$this->assertSame([
			'modifier' => ['param'],
			'modifier2' => ['param'],
		], $values);
	}

	public function testArray() {
		$values = ModifierParser::parse('modifier:[id: param]')->getValues();
		$this->assertSame([
			'modifier' => [
				0 => [
					'id' => 'param',
				]
			]
		], $values);

		$values = ModifierParser::parse('modifier:[id: param, id2: param]')->getValues();
		$this->assertSame([
			'modifier' => [
				0 => [
					'id' => 'param',
					'id2' => 'param',
				]
			]
		], $values);

		$values = ModifierParser::parse('modifier:[id: [id: param], id2: param]')->getValues();
		$this->assertSame([
			'modifier' => [
				[
					'id' => [
						'id' => 'param',
					],
					'id2' => 'param',
				]
			]
		], $values);


		$values = ModifierParser::parse('modifier:[id: [id: param], id2: param]|modifier2')->getValues();
		$this->assertSame([
			'modifier' => [
				0 => [
					'id' => [
						'id' => 'param',
					],
					'id2' => 'param',
				],
			],
			'modifier2' => [],
		], $values);
	}

	public function testNull() {
		$values = ModifierParser::parse('modifier:param,null')->getValues();

		$this->assertSame([
			'modifier' => [
				'param', null,
			]
		], $values);
	}

	public function testVariable() {
		$values = ModifierParser::parse('modifier:$1,$2');

		$this->assertSame([
			'modifier' => [1, 2],
		], $values->call([1, 2]));
	}

	public function testSameVariable() {
		$values = ModifierParser::parse('modifier:$1,$1');

		$this->assertSame([
			'modifier' => [1, 1],
		], $values->call([1]));
	}

}
