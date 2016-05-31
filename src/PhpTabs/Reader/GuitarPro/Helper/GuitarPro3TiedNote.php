<?php

namespace PhpTabs\Reader\GuitarPro\Helper;

use PhpTabs\Model\Track;

class GuitarPro3TiedNote extends AbstractReader
{
  /**
   * @param integer $string String on which note has started
   * @param Track $track
   *
   * @return integer tied note value
   */
  public function getTiedNoteValue($string, Track $track)
  {
    $measureCount = $track->countMeasures();

    if ($measureCount > 0)
    {
      for ($m = $measureCount - 1; $m >= 0; $m--)
      {
        $measure = $track->getMeasure($m);

        for ($b = $measure->countBeats() - 1; $b >= 0; $b--)
        {
          $beat = $measure->getBeat($b);
          $voice = $beat->getVoice(0);  

          for ($n = 0; $n < $voice->countNotes(); $n++)
          {
            $note = $voice->getNote($n);

            if ($note->getString() == $string)
            {
              return $note->getValue();
            }
          }
        }
      }
    }

    return -1;
  }
}