<?php

declare(strict_types=1);

namespace Ifsware\Spotlight\Tests\Stubs;

use Filament\Resources\Pages\ManageRecords;
use Ifsware\Spotlight\Concerns\HasIfswareSpotlightPageActions;

/**
 * PHPStan stub — allows HasIfswareSpotlightPageActions to be analysed in context.
 * This class is never instantiated at runtime.
 */
final class PageWithIfswareSpotlightActionsStub extends ManageRecords
{
    use HasIfswareSpotlightPageActions;

    protected static string $resource = '';
}
