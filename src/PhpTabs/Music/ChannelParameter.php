<?php

/*
 * This file is part of the PhpTabs package.
 *
 * Copyright (c) landrok at github.com/landrok
 *
 * For the full copyright and license information, please see
 * <https://github.com/stdtabs/phptabs/blob/master/LICENSE>.
 */

namespace PhpTabs\Music;

/**
 * ChannelParameter
 */
class ChannelParameter
{
  private $key;
  private $value;

  /**
   * @return int
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * @param int $key
   */
  public function setKey($key)
  {
    $this->key = $key;
  }

  /**
   * @return int
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @param int $value
   */
  public function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * @param \PhpTabs\Music\ChannelParameter $channelParameter
   */
  public function copyFrom(ChannelParameter $channelParameter)
  {
    $this->setKey($channelParameter->getKey());
    $this->setValue($channelParameter->getValue());
  }

  /**
   * @return \PhpTabs\Music\ChannelParameter
   */
  public function __clone()
  {
    return (new ChannelParameter())->copyFrom($this);
  }
}
