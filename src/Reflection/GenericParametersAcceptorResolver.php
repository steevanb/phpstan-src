<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

class GenericParametersAcceptorResolver
{

	/**
	 * @api
	 * @param \PHPStan\Type\Type[] $argTypes
	 */
	public static function resolve(array $argTypes, ParametersAcceptor $parametersAcceptor): ParametersAcceptor
	{
		$typeMap = TemplateTypeMap::createEmpty();

		foreach ($parametersAcceptor->getParameters() as $i => $param) {
			if (isset($argTypes[$i])) {
				$argType = $argTypes[$i];
			} elseif ($param->getDefaultValue() !== null) {
				$argType = $param->getDefaultValue();
			} else {
				break;
			}

			$paramType = $param->getType();
			$typeMap = $typeMap->union($paramType->inferTemplateTypes($argType));
		}

		return new ResolvedFunctionVariant(
			$parametersAcceptor,
			new TemplateTypeMap(array_merge(
				$parametersAcceptor->getTemplateTypeMap()->map(static function (string $name, Type $type): Type {
					return new ErrorType();
				})->getTypes(),
				$typeMap->getTypes()
			))
		);
	}

}
