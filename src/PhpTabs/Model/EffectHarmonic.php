<?php

namespace PhpTabs\Model;

class EffectHarmonic
{
  const KEY_NATURAL = "N.H";
  const KEY_ARTIFICIAL = "A.H";
  const KEY_TAPPED = "T.H";
  const KEY_PINCH = "P.H";
  const KEY_SEMI = "S.H";

  const TYPE_NATURAL = 1;
  const TYPE_ARTIFICIAL = 2;
  const TYPE_TAPPED = 3;
  const TYPE_PINCH = 4;
  const TYPE_SEMI = 5;
  const MIN_ARTIFICIAL_OFFSET = -24;
  const MAX_ARTIFICIAL_OFFSET = 24;
  const MAX_TAPPED_OFFSET = 24;

  public static $naturalFrequencies = array(
    array(12, 12), //AH12 (+12 frets)
    array(9, 28), //AH9 (+28 frets)
    array(5, 24), //AH5 (+24 frets)
    array(7, 19), //AH7 (+19 frets)
    array(4, 28), //AH4 (+28 frets)
    array(3, 31)  //AH3 (+31 frets)
  );

  private $type;
  private $data;

  public function __construct()
  {
    $this->type = 0;
    $this->data = 0;
  }

  /**
   * @return array
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * @param array $data
   */
  public function setData($data)
  {
    $this->data = $data;
  }

  /**
   * @return int
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @param int $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * @return bool
   */
  public function isNatural()
  {
    return $this->type == EffectHarmonic::TYPE_NATURAL;
  }

  /**
   * @return bool
   */
  public function isArtificial()
  {
    return $this->type == EffectHarmonic::TYPE_ARTIFICIAL;
  }

  /**
   * @return bool
   */
  public function isTapped()
  {
    return $this->type == EffectHarmonic::TYPE_TAPPED;
  }

  /**
   * @return bool
   */
  public function isPinch()
  {
    return $this->type == EffectHarmonic::TYPE_PINCH;
  }

  /**
   * @return bool
   */
  public function isSemi()
  {
    return $this->type == EffectHarmonic::TYPE_SEMI;
  }

  /**
   * @return \PhpTabs\Model\EffectHarmonic
   */
  public function  __clone()
  {
    $effect = new EffectHarmonic();
    $effect->setType($this->getType());
    $effect->setData($this->getData());
    return $effect;
  }
}
