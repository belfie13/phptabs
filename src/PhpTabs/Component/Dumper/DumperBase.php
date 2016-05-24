<?php

namespace PhpTabs\Component\Dumper;

/**
 * Helpers for Dumper
 */
abstract class DumperBase extends DumperEffects
{
  protected function dumpSong()
  {
    $content = array(
      'name'          => $this->song->getName(),
      'artist'        => $this->song->getArtist(),
      'album'         => $this->song->getAlbum(),
      'author'        => $this->song->getAuthor(),
      'copyright'     => $this->song->getCopyright(),
      'writer'        => $this->song->getWriter(),
      'comments'      => $this->song->getComments(),
      'channels'      => array(),
      'measureHeaders'=> array(),
      'tracks'        => array()
    );

    $countChannels = $this->song->countChannels();

    for($i=0; $i<$countChannels; $i++)
    {
      $content['channels'][$i] = $this->dumpChannel($i);
    }

    $countMeasureHeaders = $this->song->countMeasureHeaders();

    for($i=0; $i<$countMeasureHeaders; $i++)
    {
      $content['measureHeaders'][$i] = $this->dumpMeasureHeader(
        $this->song->getMeasureHeader($i)
      );
    }

    $countTracks = $this->song->countTracks();

    for($i=0; $i<$countTracks; $i++)
    {
      $content['tracks'][$i] = $this->dumpTrack($i);
    }

    return array('song' => $content);
  }

  protected function dumpTrack($index)
  {
    $track = $this->song->getTrack($index);

    $content = array(
      'number'    => $track->getNumber(),
      'offset'    => $track->getOffset(),
      'channelId' => $track->getChannelId(),
      'solo'      => $track->isSolo(),
      'mute'      => $track->isMute(),
      'name'      => $track->getName(),
      'color'     => array(
          'R' => $track->getColor()->getR(),
          'G' => $track->getColor()->getG(),
          'B' => $track->getColor()->getB()
      ),
      'lyrics'    => array(
          'from'    => $track->getLyrics()->getFrom(),
          'lyrics'  => $track->getLyrics()->getLyrics()
      ),
      'measures'  => array(),
      'strings'   => array()
    );

    $countMeasures = $track->countMeasures();

    for($i=0; $i<$countMeasures; $i++)
    {
      $content['measures'][$i] = $this->dumpMeasure(
        $track->getMeasure($i),
        $this->song->getMeasureHeader($i)
      );
    }

    $countStrings = $track->countStrings();

    for($i=0; $i<$countStrings; $i++)
    {
      $content['strings'][$i] = $this->dumpString($track->getString($i+1));
    }

    return $content;
  }

  protected function dumpChannel($index)
  {
    $channel = $this->song->getChannel($index);

    $content = array(
      'channelId' => $channel->getChannelId(),
      'name'      => $channel->getName(),
      'bank'      => $channel->getBank(),
      'program'   => $channel->getProgram(),
      'volume'    => $channel->getVolume(),
      'balance'   => $channel->getBalance(),
      'chorus'    => $channel->getChorus(),
      'reverb'    => $channel->getReverb(),
      'phaser'    => $channel->getPhaser(),
      'tremolo'   => $channel->getTremolo(),
      'parameters'=> array()
    );

    $countParameters = $channel->countParameters();

    for($i=0; $i<$countParameters; $i++)
    {
      $content['parameters'][$i] = array(
        'key'   => $channel->getParameter($i)->getKey(),
        'value' => $channel->getParameter($i)->getValue()
      );
    }

    return $content;
  }

  protected function dumpMeasure($measure, $measureHeader)
  {
    $content = array(
      'channelId'     => $measure->getTrack()->getChannelId(),
      'clef'          => $measure->getClef(),
      'keySignature'  => $measure->getKeySignature(),
      'header'        => $this->dumpMeasureHeader($measureHeader),
      'keySignature'  => $measure->getKeySignature(),
      'beats'         => array()
    );

    $countBeats = $measure->countBeats();

    for($i=0; $i<$countBeats; $i++)
    {
      $content['beats'][$i] = $this->dumpBeat($measure->getBeat($i));
    }

    return $content;
  }

  protected function dumpBeat($beat)
  {
    $content = array(
      'start'     => $beat->getStart(),
      'chord'     => $this->dumpChord($beat->getChord()),
      'text'      => $this->dumpText($beat->getText()),
      'voices'    => array(),
      'stroke'    => array(
        'direction' => $beat->getStroke()->getDirection(),
        'value'     => $beat->getStroke()->getValue()
      )
    );

    $countVoices = $beat->countVoices();

    for($i=0; $i<$countVoices; $i++)
    {
      $content['voices'][$i] = $this->dumpVoice($beat->getVoice($i));
    }

    return $content;
  }

  protected function dumpVoice($voice)
  {
    $content = array(
      'duration' => $this->dumpDuration($voice->getDuration()),
      'index'    => $voice->getIndex(),
      'empty'    => $voice->isEmpty(),
      'direction'=> $voice->getDirection(),
      'notes'    => array()
    );

    $countNotes = $voice->countNotes();

    for($i=0; $i<$countNotes; $i++)
    {
      $content['notes'][$i] = $this->dumpNote($voice->getNote($i));
    }

    return $content;
  }

  protected function dumpDuration($duration)
  {
    return array(
      'value'        => $duration->getValue(),
      'dotted'       => $duration->isDotted(),
      'doubleDotted' => $duration->isDoubleDotted(),
      'divisionType' => array(
        'enters'  => $duration->getDivision()->getEnters(),
        'times'   => $duration->getDivision()->getTimes()
      )
    );
  }

  protected function dumpNote($note)
  {
    return array(
      'value'     => $note->getValue(),
      'velocity'  => $note->getVelocity(),
      'string'    => $note->getString(),
      'tiedNote'  => $note->isTiedNote(),
      'effect'    => $this->dumpEffect($note->getEffect())
    );
  }

  protected function dumpString($string)
  {
    return is_object($string) ? array(
      'number'  => $string->getNumber(),
      'value'   => $string->getValue()
    ) : null;
  }

  protected function dumpMeasureHeader($header)
  {
    return array(
      'number'        => $header->getNumber(),
      'start'         => $header->getStart(),
      'timeSignature' => $this->dumpTimeSignature($header->getTimeSignature()),
      'tempo'         => $header->getTempo()->getValue(),
      'marker'        => $this->dumpMarker($header->getMarker()),
      'repeatOpen'     => $header->isRepeatOpen(),
      'repeatAlternative' => $header->getRepeatAlternative(),
      'repeatClose'   => $header->getRepeatClose(),
      'tripletFeel'   => $header->getTripletFeel()
    );
  }

  protected function dumpChord($chord)
  {
    if(!is_object($chord))
    {
      return null;
    }

    $content = array(
      'firstFret'  => $chord->getFirstFret(),
      'name'       => $chord->getName(),
      'strings'    => array()
    );

    $countStrings = $chord->countStrings();
    $strings = $chord->getStrings();

    for($i=0; $i<$countStrings; $i++)
    {
      $content['strings'][] = array('string' => $strings[$i]);
    }

    return $content;
  }

  protected function dumpTimeSignature($timeSignature)
  {
    return array(
        'numerator'   => $timeSignature->getNumerator(), 
        'denominator' => $this->dumpDuration($timeSignature->getDenominator())
    );
  }

  protected function dumpMarker($marker)
  {
    return is_object($marker) ? array(
      'measure' => $marker->getMeasure(), 
      'title'   => $marker->getTitle(),
      'color'   => array(
        'R' => $marker->getColor()->getR(),
        'G' => $marker->getColor()->getG(),
        'B' => $marker->getColor()->getB()
      )
    ) : null;
  }

  protected function dumpText($text)
  {
    return is_object($text) ? array(
      'value' => $text->getValue()
    ) : null;
  }
}