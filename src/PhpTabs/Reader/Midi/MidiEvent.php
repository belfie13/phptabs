<?php

namespace PhpTabs\Reader\Midi;

/**
 * Midi event
 */
class MidiEvent
{
  private $tick;
  private $message;

  /**
   * @param \PhpTabs\Reader\Midi\MidiMessage $message
   * @param mixed $tick
   */
  public function __construct(MidiMessage $message, $tick)
  {
    $this->message = $message;
    $this->tick = $tick;
  }

  /**
   * @return \PhpTabs\Reader\Midi\MidiMessage
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * @return mixed
   */
  public function getTick()
  {
    return $this->tick;
  }
}
