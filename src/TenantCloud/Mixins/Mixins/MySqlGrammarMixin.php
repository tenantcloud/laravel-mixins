<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Database\Query\Grammars\MySqlGrammar;

/**
 * @mixin MySqlGrammar
 */
class MySqlGrammarMixin
{
	public function compileCase(): callable
	{
		return function ($whens, $field = null, $else = null) {
			if (empty($whens)) {
				return null;
			}

			$sql = 'case ';

			if ($field !== null) {
				$sql .= $field . ' ';
			}

			foreach ($whens as $when => $then) {
				if (is_array($then)) {
					$when = $then['key'];
					$then = $then['value'];
				}

				$sql .= 'when ' . $this->parameter($when) . ' then ' . $this->parameter($then) . ' ';
			}

			if ($else !== null) {
				$sql .= $else . ' ';
			}

			return $sql . 'end';
		};
	}
}
