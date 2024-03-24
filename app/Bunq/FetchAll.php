<?php
declare(strict_types=1);

namespace App\Bunq;

use bunq\Exception\TooManyRequestsException;
use bunq\Http\BunqResponse;
use bunq\Http\Pagination;
use bunq\Model\Core\BunqModel;
use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

final readonly class FetchAll
{
    /**
     * @template T of BunqModel
     * @param callable(array $params): BunqResponse $callback
     * @return Generator<T>
     */
    public static function execute(callable $callback, bool $reversed = false): Generator
    {
        $page = (new Pagination());
        $page->setCount(200);

        $response = $callback($page->getUrlParamsCountOnly());

        yield from $response->getValue();

        while (($page = $response->getPagination())->getOlderId()) {
            $response = $callback($reversed ? $page->getUrlParamsPreviousPage() : $page->getUrlParamsNextPage());

            yield from $response->getValue();

            Sleep::for(1)->seconds();
        }
    }
}
