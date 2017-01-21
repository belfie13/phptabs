<?php

namespace PhpTabs\Reader\GuitarPro\Helper;

use PhpTabs\Model\Beat;
use PhpTabs\Model\Chord;

class GuitarPro3Chord extends AbstractReader
{
  /**
   * Read Chord informations
   * 
   * @param integer $strings
   * @param \PhpTabs\Model\Beat $beat
   */
  public function readChord($strings, Beat $beat)
  {
    $chord = new Chord($strings);
    $header = $this->reader->readUnsignedByte();

    if (($header & 0x01) == 0)
    {
      $chord->setName($this->reader->readStringByteSizeOfInteger());
      $chord->setFirstFret($this->reader->readInt());

      if ($chord->getFirstFret() != 0)
      {
        $this->readStrings($chord);
      }
    }
    else
    {
      $this->reader->skip(25);
      $chord->setName($this->reader->readStringByte(34));
      $chord->setFirstFret($this->reader->readInt());
     
      $this->readStrings($chord);

      $this->reader->skip(36);
    }

    if ($chord->countNotes() > 0)
    {
      $beat->setChord($chord);
    }
  }

  /**
   * @param \PhpTabs\Model\Chord $chord
   */
  private function readStrings(Chord $chord)
  {
    for ($i = 0; $i < 6; $i++)
    {
      $fret = $this->reader->readInt();

      if ($i < $chord->countStrings())
      {
        $chord->addFretValue($i, $fret);
      }
    }
  }
}
