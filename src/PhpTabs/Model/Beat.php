<?php

namespace PhpTabs\Model;

/**
 * @uses Chord
 * @uses Duration
 * @uses Measure
 * @uses Stroke
 * @uses Text
 * @uses Voice
 */
class Beat
{
  /** @const MAX_VOICES Number of voices to set */
  const MAX_VOICES = 2;

  private $start;
  private $measure;
  private $chord;
  private $text;
  private $voices;
  private $stroke;

  public function __construct()
  {
    $this->start = Duration::QUARTER_TIME;
    $this->stroke = new Stroke();
    $this->voices = array();
    for ($i = 0; $i < Beat::MAX_VOICES; $i++)
    {
      $this->setVoice($i, new Voice($i));
    }
  }

  /**
   * @return \PhpTabs\Model\Measure
   */
  public function getMeasure()
  {
    return $this->measure;
  }

  /**
   * @param \PhpTabs\Model\Measure $measure
   */
  public function setMeasure(Measure $measure)
  {
    $this->measure = $measure;
  }

  /**
   * @return int
   */
  public function getStart()
  {
    return $this->start;
  }

  /**
   * @param int $start
   */
  public function setStart($start)
  {
    $this->start = $start;
  }

  /**
   * @param int $index
   * @param \PhpTabs\Model\Voice $voice
   */
  public function setVoice($index, Voice $voice)
  {
    if ($index >= 0)
    {
      $this->voices[$index] = $voice;
      $this->voices[$index]->setBeat($this);
    }
  }

  /**
   * @param int $index
   *
   * @return \PhpTabs\Model\Voice
   */
  public function getVoice($index)
  {
    if ($index >= 0 && $index < count($this->voices))
    {
      return $this->voices[$index];
    }

    return null;
  }

  /**
   * @return int
   */
  public function countVoices()
  {
    return count($this->voices);
  }

  /**
   * @param \PhpTabs\Model\Chord $chord
   */
  public function setChord(Chord $chord)
  {
    $this->chord = $chord;
    $this->chord->setBeat($this);
  }

  /**
   * @return \PhpTabs\Model\Chord
   */
  public function getChord()
  {
    return $this->chord;
  }

  public function removeChord()
  {
    $this->chord = null;
  }

  /**
   * @return \PhpTabs\Model\Text
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * @param \PhpTabs\Model\Text $text
   */
  public function setText(Text $text)
  {
    $this->text = $text;
    $this->text->setBeat($this);
  }

  public function removeText()
  {
    $this->text = null;
  }

  /**
   * @return bool
   */
  public function isChordBeat()
  {
    return $this->chord !== null;
  }

  /**
   * @return bool
   */
  public function isTextBeat()
  {
    return $this->text !== null;
  }

  /**
   * @return \PhpTabs\Model\Stroke
   */
  public function getStroke()
  {
    return $this->stroke;
  }

  /**
   * @return bool
   */
  public function isRestBeat()
  {
    for ($v = 0; $v < $this->countVoices(); $v++)
    {
      $voice = $this->getVoice($v);

      if (!$voice->isEmpty() && !$voice->isRestVoice())
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @return \PhpTabs\Model\Beat
   */
  public function __clone()
  {
    $beat = new Beat();
    $beat->setStart($this->getStart());
    $beat->getStroke()->copyFrom($this->getStroke());

    for ($i = 0; $i < count($this->voices); $i++)
    {
      $beat->setVoice($i, clone $this->voices[$i]);
    }
    
    if ($this->chord !== null)
    {
      $beat->setChord(clone $this->chord);
    }

    if ($this->text !== null)
    {
      $beat->setText(clone $this->text);
    }

    return $beat;
  }
}
