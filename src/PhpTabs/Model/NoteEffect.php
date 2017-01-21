<?php

namespace PhpTabs\Model;

/**
 * @uses EffectBend
 * @uses EffectTremoloBar
 * @uses EffectHarmonic
 * @uses EffectGrace
 * @uses EffectTrill
 * @uses EffectTremoloPicking
 */
class NoteEffect extends NoteEffectBase
{
  protected $bend;
  protected $grace;
  protected $harmonic;
  protected $tremoloBar;
  protected $tremoloPicking;
  protected $trill;

  public function __construct()
  {
    parent::__construct();

    $this->bend = null;
    $this->harmonic = null;
    $this->grace = null;
    $this->trill = null;
    $this->tremoloBar = null;
    $this->tremoloPicking = null;
  }

  /**
   * @param bool $deadNote
   */
  public function setDeadNote($deadNote)
  {
    $this->deadNote = $deadNote;

    if ($this->isDeadNote())
    {
      $this->bend = null;
      $this->trill = null;
      $this->slide = false;
      $this->hammer = false;
      $this->tremoloBar = null;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @param bool $vibrato
   */
  public function setVibrato($vibrato)
  {
    $this->vibrato = $vibrato;

    if ($this->isVibrato())
    {
      $this->trill = null;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @return \PhpTabs\Model\EffectBend
   */
  public function getBend()
  {
    return $this->bend;
  }

  /**
   * @param \PhpTabs\Model\EffectBend $bend
   */
  public function setBend(EffectBend $bend = null)
  {
    $this->bend = $bend;

    if ($this->isBend())
    {
      $this->trill = null;
      $this->deadNote = false;
      $this->slide = false;
      $this->hammer = false;
      $this->tremoloBar = null;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @return \PhpTabs\Model\EffectTremoloBar
   */
  public function getTremoloBar()
  {
    return $this->tremoloBar;
  }

  /**
   * @param \PhpTabs\Model\EffectTremoloBar $tremoloBar
   */
  public function setTremoloBar(EffectTremoloBar $tremoloBar = null)
  {
    $this->tremoloBar = $tremoloBar;

    if ($this->isTremoloBar())
    {
      $this->bend = null;
      $this->trill = null;
      $this->deadNote = false;
      $this->slide = false;
      $this->hammer = false;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @return \PhpTabs\Model\EffectTrill
   */
  public function getTrill()
  {
    return $this->trill;
  }

  /**
   * @param \PhpTabs\Model\EffectTrill $trill
   */
  public function setTrill(EffectTrill $trill = null)
  {
    $this->trill = $trill;

    if ($this->isTrill())
    {
      $this->bend = null;
      $this->tremoloBar = null;
      $this->tremoloPicking = null;
      $this->slide = false;
      $this->hammer = false;
      $this->deadNote = false;
      $this->vibrato = false;
    }
  }

  /**
   * @return \PhpTabs\Model\EffectTremoloPicking
   */
  public function getTremoloPicking()
  {
    return $this->tremoloPicking;
  }

  /**
   * @param \PhpTabs\Model\EffectTremoloPicking $tremoloPicking
   */
  public function setTremoloPicking(EffectTremoloPicking $tremoloPicking = null)
  {
    $this->tremoloPicking = $tremoloPicking;

    if ($this->isTremoloPicking())
    {
      $this->trill = null;
      $this->bend = null;
      $this->tremoloBar = null;
      $this->slide = false;
      $this->hammer = false;
      $this->deadNote = false;
      $this->vibrato = false;
    }
  }

  /**
   * @param bool $hammer
   */
  public function setHammer($hammer)
  {
    $this->hammer = $hammer;

    if ($this->isHammer())
    {
      $this->trill = null;
      $this->bend = null;
      $this->deadNote = false;
      $this->slide = false;
      $this->tremoloBar = null;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @param bool $slide
   */
  public function setSlide($slide)
  {
    $this->slide = $slide;

    if ($this->isSlide())
    {
      $this->trill = null;
      $this->bend = null;
      $this->deadNote = false;
      $this->hammer = false;
      $this->tremoloBar = null;
      $this->tremoloPicking = null;
    }
  }

  /**
   * @param bool $ghostNote
   */
  public function setGhostNote($ghostNote)
  {
    $this->ghostNote = $ghostNote;

    if ($this->isGhostNote())
    {
      $this->accentuatedNote = false;
      $this->heavyAccentuatedNote = false;
    }
  }

  /**
   * @param bool $accentuatedNote
   */
  public function setAccentuatedNote($accentuatedNote)
  {
    $this->accentuatedNote = $accentuatedNote;

    if ($this->isAccentuatedNote())
    {
      $this->ghostNote = false;
      $this->heavyAccentuatedNote = false;
    }
  }

  /**
   * @param bool $heavyAccentuatedNote
   */
  public function setHeavyAccentuatedNote($heavyAccentuatedNote)
  {
    $this->heavyAccentuatedNote = $heavyAccentuatedNote;

    if ($this->isHeavyAccentuatedNote())
    {
      $this->ghostNote = false;
      $this->accentuatedNote = false;
    }
  }

  /**
   * @param \PhpTabs\Model\EffectHarmonic $harmonic
   */
  public function setHarmonic(EffectHarmonic $harmonic = null)
  {
    $this->harmonic = $harmonic;
  }

  /**
   * @return \PhpTabs\Model\EffectHarmonic
   */
  public function getHarmonic()
  {
    return $this->harmonic;
  }

  /**
   * @return \PhpTabs\Model\EffectGrace
   */
  public function getGrace()
  {
    return $this->grace;
  }

  /**
   * @param \PhpTabs\Model\EffectGrace $grace
   */
  public function setGrace(EffectGrace $grace = null)
  {
    $this->grace = $grace;
  }

  /**
   * @param bool $palmMute
   */
  public function setPalmMute($palmMute)
  {
    $this->palmMute = $palmMute;

    if ($this->isPalmMute())
    {
      $this->staccato = false;
      $this->letRing = false;
    }
  }

  /**
   * @param bool $staccato
   */
  public function setStaccato($staccato)
  {
    $this->staccato = $staccato;

    if ($this->isStaccato())
    {
      $this->palmMute = false;
      $this->letRing = false;
    }
  }

  /**
   * @param bool $letRing
   */
  public function setLetRing($letRing)
  {
    $this->letRing = $letRing;

    if ($this->isLetRing())
    {
      $this->staccato = false;
      $this->palmMute = false;
    }
  }

  /**
   * @param bool $popping
   */
  public function setPopping($popping)
  {
    $this->popping = $popping;

    if ($this->isPopping())
    {
      $this->tapping = false;
      $this->slapping = false;
    }
  }

  /**
   * @param bool $slapping
   */
  public function setSlapping($slapping)
  {
    $this->slapping = $slapping;

    if ($this->isSlapping())
    {
      $this->tapping = false;
      $this->popping = false;
    }
  }

  /**
   * @param bool $tapping
   */
  public function setTapping($tapping)
  {
    $this->tapping = $tapping;

    if ($this->isTapping())
    {
      $this->slapping = false;
      $this->popping = false;
    }
  }

  /**
   * @param bool $fadeIn
   */
  public function setFadeIn($fadeIn)
  {
    $this->fadeIn = $fadeIn;
  }

  /**
   * @return \PhpTabs\Model\NoteEffect
   */
  public function __clone()
  {
    $effect = new NoteEffect();
    $attrs = get_object_vars($this);

    foreach ($attrs as $attr => $value)
    {
      $setter = sprintf('set%s', ucfirst($attr));
      $getter = sprintf('is%s', ucfirst($attr));

      $effect->$setter(
        in_array($attr, array('bend', 'tremoloBar', 'harmonic', 'grace', 'trill', 'tremoloPicking'))
          ? ($this->$getter() ? clone $value : null)
          : $effect->$setter($this->$getter())
      );
    }

    return $effect;
  }	
}
