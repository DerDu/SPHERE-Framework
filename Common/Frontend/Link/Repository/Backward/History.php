<?php
namespace SPHERE\Common\Frontend\Link\Repository\Backward;

/**
 * Class History
 *
 * @package SPHERE\Common\Frontend\Link\Repository\Backward
 */
class History
{

    /** @var Step[] $StepStack */
    private $StepStack = array();

    /**
     * Get current Back-Step
     *
     * @return false|Step
     */
    public function getStep()
    {

        if (!empty( $this->StepStack )) {
            if (( $Count = count($this->StepStack) ) == 1) {
                return false;
            } else {
                return $this->StepStack[$Count - 2];
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getStack()
    {

        $Result = array();
        if (!empty( $this->StepStack )) {
            array_walk($this->StepStack, function (Step $Step) use (&$Result) {

                $Result[] = $Step->getRoute();
            });
        }
        return $Result;
    }

    /**
     * Add Step if not equals last History-Step
     *
     * @param Step $Step
     *
     * @return bool|null true - added, false - removed, null - not handled
     */
    public function setStep(Step $Step)
    {

        if( preg_match('!^/Api/!is', $Step->getRoute() ) ) {
            return null;
        }

        // Shrink History
        $this->shrinkHistory(8);

        $Last = $this->getLastStep();
        // Is not a GoBack Step
        if (!$Step->isGoBack()) {
            // Stack is empty
            if (!$Last && $Step->isValid()) {
                // Make Step > GoBack & Add to Stack
                $Step->setGoBack();
                array_push($this->StepStack, $Step);
                return true;

            } // Stack is not empty
            elseif ($Step->isValid() && $Last->getRoute() != $Step->getRoute()) {
                // Make Step > GoBack & Add to Stack
                $Step->setGoBack();
                array_push($this->StepStack, $Step);
                return true;
            }
        } else {
            if (
                ( $Last->getPath() != $Step->getPath() )
                || ( $Last->getCleanData() != $Step->getCleanData() )
            ) {
                $this->removeStep($Last);
                return false;
            }
        }
        // Step not handled
        return null;
    }

    /**
     * Remove oldest Step if History is to large
     *
     * @param int $ToSize 4
     *
     * @return bool
     */
    private function shrinkHistory($ToSize = 4)
    {

        if (count($this->StepStack) > $ToSize) {
            array_shift($this->StepStack);
            return true;
        }
        return false;
    }

    /**
     * Get last History-Step
     *
     * @return false|Step
     */
    private function getLastStep()
    {

        if (!empty( $this->StepStack )) {
            return end($this->StepStack);
        }
        return false;
    }

    /**
     * Remove Step if is last History-Step
     *
     * @param Step $Step
     *
     * @return bool
     */
    private function removeStep(Step $Step)
    {

        $Last = $this->getLastStep();
        if ($Last && $Last->getRoute() == $Step->getRoute()) {
            array_pop($this->StepStack);
            return true;
        }
        return false;
    }
}
