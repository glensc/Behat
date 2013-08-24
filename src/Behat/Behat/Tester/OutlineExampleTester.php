<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\OutlineNode;

/**
 * Outline example tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleTester extends IsolatedStepCollectionTester
{
    /**
     * Tests outline example.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param integer              $iteration
     * @param array                $tokens
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $iteration,
        array $tokens
    )
    {
        $status = StepEvent::PASSED;

        $contexts = $this->initializeContextPool($suite, $contexts);

        $event = new OutlineExampleEvent($suite, $contexts, $outline, $iteration);
        $this->dispatch(EventInterface::HOOKABLE_BEFORE_SCENARIO, $event);
        $this->dispatch(EventInterface::BEFORE_OUTLINE_EXAMPLE, $event);

        if ($outline->getFeature()->hasBackground()) {
            $background = $outline->getFeature()->getBackground();

            $tester = $this->getBackgroundTester($suite, $contexts, $background);
            $status = $tester->test($suite, $outline, $background, $contexts);
        }

        foreach ($outline->getSteps() as $step) {
            $step = $step->createExampleRowStep($tokens);

            $tester = $this->getStepTester($suite, $contexts, $step, $status);
            $status = max($status, $tester->test($suite, $contexts, $step, $outline));
        }

        $event = new OutlineExampleEvent($suite, $contexts, $outline, $iteration, $status);
        $this->dispatch(EventInterface::AFTER_OUTLINE_EXAMPLE, $event);
        $this->dispatch(EventInterface::HOOKABLE_AFTER_SCENARIO, $event);

        return $status;
    }
}