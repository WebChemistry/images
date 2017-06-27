<?php
namespace WebChemistry\Images\Tests\Parsers\Tokenizers;

use WebChemistry\Images\Parsers\Tokenizers\ModifierTokenizer;
use WebChemistry\Images\Parsers\Tokenizers\Token;
use WebChemistry\Testing\TUnitTest;

class ModifierTokenizerTest extends \Codeception\Test\Unit {

	use TUnitTest;

	public function testBasic() {
		$tokenizer = new ModifierTokenizer('modifier:param1|modifier');
		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame('modifier', $token->token);
		$this->assertSame(Token::VALUE, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame(':', $token->token);
		$this->assertSame(Token::COLON, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame('param1', $token->token);
		$this->assertSame(Token::VALUE, $token->type);
	}

	public function testParameters() {
		$tokenizer = new ModifierTokenizer('modifier:param1,param2');
		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame('modifier', $token->token);
		$this->assertSame(Token::VALUE, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame(':', $token->token);
		$this->assertSame(Token::COLON, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame('param1', $token->token);
		$this->assertSame(Token::VALUE, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame(',', $token->token);
		$this->assertSame(Token::COMMA, $token->type);

		$this->assertInstanceOf(Token::class, $token = $tokenizer->nextToken());
		$this->assertSame('param2', $token->token);
		$this->assertSame(Token::VALUE, $token->type);

		$this->assertNull($tokenizer->nextToken());
	}

	public function testArray() {
		$tokenizer = new ModifierTokenizer('modifier:[param1: [param2: param3]]');

		$tokenizer->nextToken();
		$tokenizer->nextToken();

		$token = $tokenizer->nextToken();
		$this->assertSame('[', $token->token);
		$this->assertSame(Token::BRACKET_LEFT, $token->type);

		$tokenizer->nextToken();
		$tokenizer->nextToken();

		$token = $tokenizer->nextToken();
		$this->assertSame('[', $token->token);
		$this->assertSame(Token::BRACKET_LEFT, $token->type);
	}

}
