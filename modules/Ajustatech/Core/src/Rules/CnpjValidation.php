<?php

namespace Ajustatech\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjValidation implements ValidationRule
{

	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		//criar validaçao de cnpj
		$cnpj = preg_replace('/[^0-9]/', '', $value);

		if (strlen($cnpj) != 14) {
			$fail("O campo :attribute é inválido.");
			return;
		}

		if (preg_match('/(\d)\1{13}/', $cnpj)) {
			$fail("O campo :attribute é inválido.");
			return;
		}

		$cnpj = str_split($cnpj);

		$dv1_original = $cnpj[12];
		$dv2_original = $cnpj[13];


		$soma = 0;
		for ($posicao = 0, $fator = 5; $posicao < 4; $posicao++, $fator--) {
			$soma = $soma + $cnpj[$posicao] * $fator;
		}
		for ($posicao = 4, $fator = 9; $posicao < 12; $posicao++, $fator--) {
			$soma = $soma + $cnpj[$posicao] * $fator;
		}

		$resto = $soma % 11;
		$dv1 = $resto < 2 ? 0 : 11 - $resto;

		$cnpj[12] = $dv1;

		$soma = 0;

		for ($posicao = 0, $fator = 6; $posicao < 5; $posicao++, $fator--) {
			$soma = $soma + $cnpj[$posicao] * $fator;
		}
		for ($posicao = 5, $fator = 9; $posicao < 13; $posicao++, $fator--) {
			$soma = $soma + $cnpj[$posicao] * $fator;
		}

		$resto = $soma % 11;
		$dv2 = $resto < 2 ? 0 : 11 - $resto;
		$cnpj[13] = $dv2;

		if ($dv1_original != $dv1 || $dv2_original != $dv2) {
			$fail("O campo :attribute é inválido.");
			return;
		}
	}
}
