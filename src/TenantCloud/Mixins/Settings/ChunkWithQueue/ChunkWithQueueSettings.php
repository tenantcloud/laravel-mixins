<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

class ChunkWithQueueSettings
{
	public QueryOptions $queryOptions;

	public ChunkOptions $chunkOptions;

	public QueueOptions $queueOptions;

	public array $handlerParameters = [];

	public function __construct(
		QueryOptions $queryOptions,
		ChunkOptions $chunkOptions,
		QueueOptions $queueOptions
	) {
		$this->queryOptions = $queryOptions;
		$this->chunkOptions = $chunkOptions;
		$this->queueOptions = $queueOptions;
	}

	public static function defaultSettings(): self
	{
		return new self(
			QueryOptions::defaultInstance(),
			ChunkOptions::defaultInstance(),
			QueueOptions::defaultInstance(),
		);
	}
}
