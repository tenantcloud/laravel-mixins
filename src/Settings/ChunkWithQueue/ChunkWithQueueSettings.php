<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

class ChunkWithQueueSettings
{
	public function __construct(
		public QueryOptions $queryOptions,
		public ChunkOptions $chunkOptions,
		public QueueOptions $queueOptions
	) {}

	public static function defaultSettings(): self
	{
		return new self(
			QueryOptions::defaultInstance(),
			ChunkOptions::defaultInstance(),
			QueueOptions::defaultInstance(),
		);
	}
}
