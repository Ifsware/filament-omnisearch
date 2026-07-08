<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Tests\Stubs;

use Filament\Resources\Pages\ManageRecords;
use Ifsware\Omnisearch\Concerns\HasOmnisearchPageActions;

/**
 * PHPStan stub — allows HasOmnisearchPageActions to be analysed in context.
 * This class is never instantiated at runtime.
 */
final class PageWithOmnisearchActionsStub extends ManageRecords
{
    use HasOmnisearchPageActions;

    protected static string $resource = '';
}
