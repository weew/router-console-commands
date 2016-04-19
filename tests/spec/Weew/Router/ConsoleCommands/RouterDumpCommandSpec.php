<?php

namespace tests\spec\Weew\Router\ConsoleCommands;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Weew\Console\IInput;
use Weew\Console\IOutput;
use Weew\Console\Output;
use Weew\ConsoleArguments\Command;
use Weew\Router\ConsoleCommands\RouterDumpCommand;
use Weew\Router\Router;

/**
 * @mixin RouterDumpCommand
 */
class RouterDumpCommandSpec extends ObjectBehavior {
    function it_is_initializable() {
        $this->shouldHaveType(RouterDumpCommand::class);
    }

    function it_setups() {
        $command = new Command();
        $this->setup($command);
        it($command->getName())->shouldBe('router:dump');
    }

    function it_runs(IInput $input, IOutput $output) {
        $router = new Router();
        $router->addFilter('filter', function() {});

        for ($i = 0; $i < 15; $i++) {
            $router->get('/some/path', 'value');
        }

        for ($i = 0; $i < 15; $i++) {
            $router->group()->get('/another/path', 'value');
        }

        $router->enableFilter('filter');

        $this->run($input, $output, $router);
    }

    function it_runs_without_routes(IInput $input, IOutput $output) {
        $router = new Router();
        $this->run($input, $output, $router);
    }
}
