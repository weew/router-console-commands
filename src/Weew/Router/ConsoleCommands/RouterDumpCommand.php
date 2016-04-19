<?php

namespace Weew\Router\ConsoleCommands;

use Weew\Console\IInput;
use Weew\Console\IOutput;
use Weew\Console\Widgets\TableWidget;
use Weew\ConsoleArguments\ICommand;
use Weew\Router\IRouter;

class RouterDumpCommand {
    /**
     * @var IInput
     */
    private $input;

    /**
     * @var IOutput
     */
    private $output;

    /**
     * @param ICommand $command
     */
    public function setup(ICommand $command) {
        $command->setName('router:dump')
            ->setDescription('Dump routes');
    }

    /**
     * @param IInput $input
     * @param IOutput $output
     * @param IRouter $router
     */
    public function run(IInput $input, IOutput $output, IRouter $router) {
        $this->input = $input;
        $this->output = $output;

        $facts = $this->gatherFacts($router);

        $this->output->writeLine(" <header>Routes: </header>");

        if (count($facts) > 0) {
            $this->renderFacts($facts);
        } else {
            $this->output->writeLineIndented("There are no routes yet");
        }
    }

    /**
     * @param IRouter $router
     *
     * @return array
     */
    private function gatherFacts(IRouter $router) {
        $facts = [];

        foreach ($router->getRoutesMatcher()->getFiltersMatcher()->getFilters() as $filter) {
            $filters[] = $filter->getName();
        }

        foreach ($router->getRoutes() as $route) {
            $filters = [];

            foreach ($router->getRoutesMatcher()->getFiltersMatcher()->getFilters() as $filter) {
                $filters[] = $filter->getName();
            }

            $facts[] = [
                'filters' => $filters,
                'hosts' => $router->getRoutesMatcher()->getRestrictionsMatcher()->getHosts(),
                'domains' => $router->getRoutesMatcher()->getRestrictionsMatcher()->getDomains(),
                'subdomains' => $router->getRoutesMatcher()->getRestrictionsMatcher()->getSubdomains(),
                'protocols' => $router->getRoutesMatcher()->getRestrictionsMatcher()->getProtocols(),
                'tlds' => $router->getRoutesMatcher()->getRestrictionsMatcher()->getTLDs(),
                'methods' => $route->getMethods(),
                'path' => $route->getPath(),
            ];
        }

        foreach ($router->getNestedRouters() as $router) {
            $facts = array_merge($facts, $this->gatherFacts($router));
        }

        return $facts;
    }

    /**
     * @param array $facts
     */
    private function renderFacts(array $facts) {
        $table = new TableWidget($this->input, $this->output);
        $section = ["Method", "Filter", "Route"];

        foreach ($facts as $index => $fact) {
            if ($index === 0) {
                $table->addRow($section);
            } else if ($index % 20 === 0) {
                $table->addRow('');
                $table->addRow($section);
            }

            $path = array_get($fact, 'path');
            $methods = implode(', ', array_get($fact, 'methods'));
            $filters = implode(', ', array_get($fact, 'filters'));

            $table->addRow(
                "<green>$methods</green>",
                "<yellow>$filters</yellow>",
                $path
            );
        }

        $table->render();
    }
}
