<?php

namespace PhpTabs\Reader\GuitarPro\Helper;

use PhpTabs\Model\Note;
use PhpTabs\Model\NoteEffect;
use PhpTabs\Model\TabString;
use PhpTabs\Model\Track;
use PhpTabs\Model\Velocities;

class GuitarPro3Note extends AbstractReader
{
  /**
   * Reads a note
   * 
   * @param \PhpTabs\Model\TabString $string
   * @param \PhpTabs\Model\Track $track
   * @param \PhpTabs\Model\NoteEffect $effect
   *
   * @return \PhpTabs\Model\Note
   */
  public function readNote(TabString $string, Track $track, NoteEffect $effect)
  {
    $flags = $this->reader->readUnsignedByte();
    $note = new Note();
    $note->setString($string->getNumber());
    $note->setEffect($effect);
    $note->getEffect()->setGhostNote((($flags & 0x04) != 0));

    if (($flags & 0x20) != 0)
    {
      $noteType = $this->reader->readUnsignedByte();
      $note->setTiedNote($noteType == 0x02);
      $note->getEffect()->setDeadNote($noteType == 0x03);
    }

    if (($flags & 0x01) != 0)
    {
      $this->reader->skip(2);
    }

    if (($flags & 0x10) != 0)
    {
      $note->setVelocity( (Velocities::MIN_VELOCITY + (Velocities::VELOCITY_INCREMENT * $this->reader->readByte())) - Velocities::VELOCITY_INCREMENT);
    }

    if (($flags & 0x20) != 0)
    {
      $fret = $this->reader->readByte();

      $value = $note->isTiedNote()
        ? $this->reader->factory('GuitarPro3TiedNote')->getTiedNoteValue($string->getNumber(), $track)
        : $fret;

      $note->setValue($value >= 0 && $value < 100 ? $value : 0);
    }

    if (($flags & 0x80) != 0)
    {
      $this->reader->skip(2);
    }

    if (($flags & 0x08) != 0)
    {
      $this->reader->factory('GuitarPro3Effects')->readNoteEffects($note->getEffect());
    }

    return $note;
  }
}
