<?php

namespace Ajustatech\Customer\Database\Models;

use Ajustatech\Customer\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'person',
		'cpf_cnpj',
		'state_registration',
		'rg',
		'issue_date',
		'issuer',
		'cellphone',
		'phone',
		'email',
		'date_of_birth',
		'marital_status',
		'zip_code',
		'address',
		'number',
		'neighborhood',
		'city',
		'state',
		'birthplace',
		'credit_limit',
		'complement',
		'fathers_name',
		'mothers_name',
		'observations',
		'status',
	];

	public static function search($search, $status = false, $limit = null): mixed
	{
		 $querry = self::searchWithoutStatus($search);

		 if($status){
			 $querry->where('status', '1');
		 }

		 if($limit){
			 $querry->take($limit);
		 }

		 return $querry->get();
	}

	public static function searchAll($status = false, $limit = null): mixed
	{
		$querry =  self::query();

		if($status){
			$querry->where('status', '1');
		}

		if($limit){
			$querry->take($limit);
		}

		return $querry->get(['*']);
	}

	private static function searchWithoutStatus($search): mixed
	{
		return self::where(function ($query) use ($search) {
			$query->where('name', 'LIKE', '%' . $search . '%')
				->orWhere('cpf_cnpj', 'LIKE', '%' . $search . '%')
				->orWhere('cellphone', 'LIKE', '%' . $search . '%')
				->orWhere('email', 'LIKE', '%' . $search . '%');
		});
	}

    protected static function newFactory(){
        return CustomerFactory::new();
    }
}
