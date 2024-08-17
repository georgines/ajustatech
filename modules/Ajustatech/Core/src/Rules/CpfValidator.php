<?php

namespace Ajustatech\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValidator implements ValidationRule
{
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{

		$cpf = preg_replace('/[^0-9]/', '', $value);


		if (strlen($cpf) != 11) {

			$fail("O campo :attribute é inválido.");
			return;
		}

		if (preg_match('/(\d)\1{10}/', $cpf)) {
			$fail("O campo :attribute é inválido.");
			return;
		}

		$cpf = str_split($cpf);

		$dv1_original = $cpf[9];
		$dv2_original = $cpf[10];
		$cpf[9] = "-1";
		$cpf[10] = "-1";

		for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--) {
			$soma += $cpf[$i] * $j;
		}

		$resto = $soma % 11;
		$dv1 = (11 - $resto) > 9 ? 0 : 11 - $resto;
		$cpf[9] = $dv1;

		for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--) {
			$soma += $cpf[$i] * $j;
		}

		$resto = $soma % 11;
		$dv2 = (11 - $resto) > 9 ? 0 : 11 - $resto;

		if ($dv1_original != $dv1 || $dv2_original != $dv2) {
			$fail( "O campo :attribute é inválido.");
			return;
		}
	}
}
