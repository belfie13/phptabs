<?php

namespace PhpTabs\Reader\Midi;

/**
 * Midi track
 */
class MidiTrack
{
  private $ticks;
  private $events = array();

  /**
   * @param \PhpTabs\Reader\Midi\MidiEvent $event
   */
  public function add(MidiEvent $event)
  {
    $this->events[] = $event;
    $this->ticks = max($this->ticks, $event->getTick());
  }

  /**
   * @param int $index
   *
   * @return \PhpTabs\Reader\Midi\MidiEvent $event
   */
  public function get($index)
  {
    return $this->events[$index];
  }

  /**
   * @return int
   */
  public function countEvents()
  {
    return count($this->events);
  }

  /**
   * @return int
   */
  public function ticks()
  {
    return $this->ticks;
  }
}
