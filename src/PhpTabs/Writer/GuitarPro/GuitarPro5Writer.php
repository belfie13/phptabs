<?php

namespace PhpTabs\Writer\GuitarPro;

use Exception;

use PhpTabs\Reader\GuitarPro\GuitarProReaderInterface as GprInterface;

use PhpTabs\Model\Beat;
use PhpTabs\Model\Channel;
use PhpTabs\Model\Chord;
use PhpTabs\Model\Color;
use PhpTabs\Model\DivisionType;
use PhpTabs\Model\Duration;
use PhpTabs\Model\EffectBend;
use PhpTabs\Model\EffectGrace;
use PhpTabs\Model\EffectHarmonic;
use PhpTabs\Model\EffectTremoloBar;
use PhpTabs\Model\EffectTremoloPicking;
use PhpTabs\Model\EffectTrill;
use PhpTabs\Model\Marker;
use PhpTabs\Model\Measure;
use PhpTabs\Model\MeasureHeader;
use PhpTabs\Model\Note;
use PhpTabs\Model\NoteEffect;
use PhpTabs\Model\Song;
use PhpTabs\Model\Stroke;
use PhpTabs\Model\Tempo;
use PhpTabs\Model\Text;
use PhpTabs\Model\TimeSignature;
use PhpTabs\Model\Track;
use PhpTabs\Model\Velocities;
use PhpTabs\Model\Voice;

class GuitarPro5Writer extends GuitarProWriterBase
{
  /** @constant version */
  const VERSION = 'FICHIER GUITAR PRO v5.00';

  private $setUpLines = array(
    '%TITLE%',
    '%SUBTITLE%',
    '%ARTIST%',
    '%ALBUM%',
    'Words by %WORDS%',
    'Music by %MUSIC%',
    'Words & Music by %WORDSMUSIC%',
    'Copyright %COPYRIGHT%',
    'All Rights Reserved - International Copyright Secured',
    'Page %N%/%P%',
    'Moderate'
  );

  public function __construct(Song $song)
  {
    parent::__construct();

    if($song->isEmpty())
    {
      throw new Exception('Song is empty');
    }

    $this->configureChannelRouter($song);
    $header = $song->getMeasureHeader(0);
    $this->writeStringByte(self::VERSION, 30);
    $this->writeInformations($song);
    $this->writeLyrics($song);
    $this->writeSetup();
    $this->writeInt($header->getTempo()->getValue());
    $this->writeInt(0);
    $this->writeByte(0);
    $this->writeChannels($song);
    for($i = 0; $i < 42; $i++)
    {
      $this->writeByte(255);
    }
    $this->writeInt($song->countMeasureHeaders());
    $this->writeInt($song->countTracks());
    $this->writeMeasureHeaders($song);
    $this->writeTracks($song);
    $this->skipBytes(2);
    $this->writeMeasures($song, clone $header->getTempo());   
  }

  private function makeChannels(Song $song)
  {
    $channels = array();
    for($i = 0; $i < 64; $i++)
    {
      $channels[$i] = new Channel();
      $channels[$i]->setProgram(
        $i == Channel::DEFAULT_PERCUSSION_CHANNEL
            ? Channel::DEFAULT_PERCUSSION_PROGRAM : 24
      );
      $channels[$i]->setVolume(13);
      $channels[$i]->setBalance(8);
      $channels[$i]->setChorus(0);
      $channels[$i]->setReverb(0);
      $channels[$i]->setPhaser(0);
      $channels[$i]->setTremolo(0);
    }

    $songChannels = $song->getChannels();

    foreach($songChannels as $channel)
    {
      $channelRoute = $this->getChannelRoute($channel->getChannelId());
      $channels[$channelRoute->getChannel1()]->setProgram($channel->getProgram());
      $channels[$channelRoute->getChannel1()]->setVolume($channel->getVolume());
      $channels[$channelRoute->getChannel1()]->setBalance($channel->getBalance());

      $channels[$channelRoute->getChannel2()]->setProgram($channel->getProgram());
      $channels[$channelRoute->getChannel2()]->setVolume($channel->getVolume());
      $channels[$channelRoute->getChannel2()]->setBalance($channel->getBalance());
    }

    return $channels;
  }

  private function parseDuration(Duration $duration)
  {
    $value = 0;
    switch($duration->getValue())
    {
      case Duration::WHOLE:
        $value = -2;
        break;
      case Duration::HALF:
        $value = -1;
        break;
      case Duration::QUARTER:
        $value = 0;
        break;
      case Duration::EIGHTH:
        $value = 1;
        break;
      case Duration::SIXTEENTH:
        $value = 2;
        break;
      case Duration::THIRTY_SECOND:
        $value = 3;
        break;
      case Duration::SIXTY_FOURTH:
        $value = 4;
        break;
    }
    return $value;
  }

  private function writeBeat(Voice $voice, Beat $beat, Measure $measure, $changeTempo)
  {
    $duration = $voice->getDuration();
    $effect = new NoteEffect();

    for($i = 0; $i < $voice->countNotes(); $i++)
    {
      $playedNote = $voice->getNote($i);

      if($playedNote->getEffect()->isFadeIn())
      {
        $effect->setFadeIn(true);
      }

      if($playedNote->getEffect()->isTremoloBar())
      {
        $effect->setTremoloBar(clone $playedNote->getEffect()->getTremoloBar());
      }

      if($playedNote->getEffect()->isTapping())
      {
        $effect->setTapping(true);
      }

      if($playedNote->getEffect()->isSlapping())
      {
        $effect->setSlapping(true);
      }

      if($playedNote->getEffect()->isPopping())
      {
        $effect->setPopping(true);
      }
    }

    $flags = 0;

    if($duration->isDotted() || $duration->isDoubleDotted()) 
    {
      $flags |= 0x01;
    }

    if($voice->getIndex() == 0 && $beat->isChordBeat())
    {
      $flags |= 0x02;
    }

    if($voice->getIndex() == 0 && $beat->isTextBeat())
    {
      $flags |= 0x04;
    }

    if($beat->getStroke()->getDirection() != Stroke::STROKE_NONE)
    {
      $flags |= 0x08;
    }
    else if( $effect->isTremoloBar() 
          || $effect->isTapping() 
          || $effect->isSlapping() 
          || $effect->isPopping() 
          || $effect->isFadeIn())
    {
      $flags |= 0x08;
    }

    if($changeTempo)
    {
      $flags |= 0x10;
    }

    if(!$duration->getDivision()->isEqual(DivisionType::normal()))
    {
      $flags |= 0x20;
    }

    if($voice->isEmpty() || $voice->isRestVoice())
    {
      $flags |= 0x40;
    }

    $this->writeUnsignedByte($flags);

    if(($flags & 0x40) != 0)
    {
      $this->writeUnsignedByte($voice->isEmpty() ? 0 : 0x02);
    }

    $this->writeByte($this->parseDuration($duration));

    if(($flags & 0x20) != 0)
    {
      $this->writeInt($duration->getDivision()->getEnters());
    }

    if (($flags & 0x02) != 0)
    {
      $this->writeChord($beat->getChord());
    }

    if(($flags & 0x04) != 0)
    {
      $this->writeText($beat->getText());
    }

    if(($flags & 0x08) != 0)
    {
      $this->writeBeatEffects($beat, $effect);
    }

    if(($flags & 0x10) != 0)
    {
      $this->writeMixChange($measure->getTempo());
    }

    $stringFlags = 0;

    if(!$voice->isRestVoice())
    {
      for($i = 0; $i < $voice->countNotes(); $i++)
      {
        $playedNote = $voice->getNote($i);
        $string = (7 - $playedNote->getString());
        $stringFlags |= (1 << $string);
      }
    }

    $this->writeUnsignedByte($stringFlags);

    for($i = 6; $i >= 0; $i--)
    {
      if(($stringFlags & (1 << $i)) != 0)
      {
        for($n = 0; $n < $voice->countNotes(); $n++)
        {
          $playedNote = $voice->getNote($n);
          if($playedNote->getString() == (6 - $i + 1))
          {
            $this->writeNote($playedNote);
            break;
          }
        }
      }
    }

    $this->skipBytes(2);
  }

  private function writeBeatEffects(Beat $beat, NoteEffect $noteEffect)
  {
    $flags1 = 0;
    $flags2 = 0;

    if($noteEffect->isFadeIn())
    {
      $flags1 |= 0x10;
    }

    if($noteEffect->isTapping() || $noteEffect->isSlapping() || $noteEffect->isPopping())
    {
      $flags1 |= 0x20;
    }

    if($noteEffect->isTremoloBar())
    {
      $flags2 |= 0x04;
    }

    if($beat->getStroke()->getDirection() != Stroke::STROKE_NONE)
    {
      $flags1 |= 0x40;
    }

    $this->writeUnsignedByte($flags1);
    $this->writeUnsignedByte($flags2);

    if (($flags1 & 0x20) != 0)
    {
      if($noteEffect->isTapping())
      {
        $this->writeUnsignedByte(1);
      }
      else if($noteEffect->isSlapping())
      {
        $this->writeUnsignedByte(2);
      }
      else if($noteEffect->isPopping())
      {
        $this->writeUnsignedByte(3);
      }
    }

    if(($flags2 & 0x04) != 0)
    {
      $this->writeTremoloBar($noteEffect->getTremoloBar());
    }

    if (($flags1 & 0x40) != 0)
    {
      $this->writeUnsignedByte(
        $beat->getStroke()->getDirection() == Stroke::STROKE_UP
          ? $this->toStrokeValue($beat->getStroke()) : 0
      );

      $this->writeUnsignedByte(
        $beat->getStroke()->getDirection() == Stroke::STROKE_DOWN
          ? $this->toStrokeValue($beat->getStroke()) : 0
      );
    }
  }

  private function writeBend(EffectBend $bend)
  {
    $points = count($bend->getPoints());
    $this->writeByte(1);
    $this->writeInt(0);
    $this->writeInt($points);

    for($i = 0; $i < $points; $i++)
    {
      $point = $bend->getPoints()[$i];
      $this->writeInt(
        intval($point->getPosition() * GprInterface::GP_BEND_POSITION / EffectBend::MAX_POSITION_LENGTH)
      );
      $this->writeInt(
        intval($point->getValue() * GprInterface::GP_BEND_SEMITONE / EffectBend::SEMITONE_LENGTH)
      );
      $this->writeByte(0);
    }
  }

  private function writeChannels(Song $song)
  {
    $channels = $this->makeChannels($song);

    for($i = 0; $i < count($channels); $i++)
    {
      $this->writeInt($channels[$i]->getProgram());
      $this->writeByte($this->toChannelByte($channels[$i]->getVolume()));
      $this->writeByte($this->toChannelByte($channels[$i]->getBalance()));
      $this->writeByte($this->toChannelByte($channels[$i]->getChorus()));
      $this->writeByte($this->toChannelByte($channels[$i]->getReverb()));
      $this->writeByte($this->toChannelByte($channels[$i]->getPhaser()));
      $this->writeByte($this->toChannelByte($channels[$i]->getTremolo()));
      $this->writeBytes(array(0, 0));
    }
  }

  private function writeChord(Chord $chord)
  {
    $this->writeBytes(
      array(
        1, 1, 0, 0,
        0, 12, 0, 0,
        -1, -1, -1, -1,
        0, 0, 0, 0, 0
      )
    );
    $this->writeStringByte($chord->getName(), 21);
    $this->skipBytes(4);
    $this->writeInt($chord->getFirstFret());

    for($i = 0; $i < 7; $i++)
    {
      $this->writeInt($i < $chord->countStrings() ? $chord->getFretValue($i) : -1);
    }

    $this->skipBytes(32);
  }

  private function writeColor(Color $color)
  {
    $this->writeUnsignedByte($color->getR());
    $this->writeUnsignedByte($color->getG());
    $this->writeUnsignedByte($color->getB());
    $this->writeByte(0);
  }

  private function writeGrace(EffectGrace $grace)
  {
    $this->writeUnsignedByte($grace->getFret());

    $this->writeUnsignedByte(
      intval((($grace->getDynamic() - Velocities::MIN_VELOCITY) / Velocities::VELOCITY_INCREMENT) + 1)
    );

    if($grace->getTransition() == EffectGrace::TRANSITION_NONE)
    {
      $this->writeUnsignedByte(0);
    }
    else if($grace->getTransition() == EffectGrace::TRANSITION_SLIDE)
    {
      $this->writeUnsignedByte(1);
    }
    else if($grace->getTransition() == EffectGrace::TRANSITION_BEND)
    {
      $this->writeUnsignedByte(2);
    }
    else if($grace->getTransition() == EffectGrace::TRANSITION_HAMMER)
    {
      $this->writeUnsignedByte(3);
    }

    $this->writeUnsignedByte($grace->getDuration());

    $this->writeUnsignedByte(
      ($grace->isDead() ? 0x01 : 0) | ($grace->isOnBeat() ? 0x02 : 0)
    );
  }

  private function writeInformations(Song $song)
  {
    $this->writeStringByteSizeOfInteger($song->getName());
    $this->writeStringByteSizeOfInteger('');
    $this->writeStringByteSizeOfInteger($song->getArtist());
    $this->writeStringByteSizeOfInteger($song->getAlbum());
    $this->writeStringByteSizeOfInteger($song->getAuthor());
    $this->writeStringByteSizeOfInteger('');
    $this->writeStringByteSizeOfInteger($song->getCopyright());
    $this->writeStringByteSizeOfInteger($song->getWriter());
    $this->writeStringByteSizeOfInteger('');

    $comments = $this->toCommentLines($song->getComments());
    $this->writeInt(count($comments));
    for($i = 0; $i < count($comments); $i++)
    {
      $this->writeStringByteSizeOfInteger($comments[$i]);
    }
  }

  private function writeLyrics(Song $song)
  {
    $lyricTrack = null;
    $tracks = $song->getTracks();

    foreach($tracks as $track)
    {
      if(!$track->getLyrics()->isEmpty())
      {
        $lyricTrack = $track;
        break;
      }
    }

    $this->writeInt($lyricTrack == null ? 0 : $lyricTrack->getNumber());
    $this->writeInt($lyricTrack == null ? 0 : $lyricTrack->getLyrics()->getFrom());
    $this->writeStringInteger(
      $lyricTrack == null ? '' : $lyricTrack->getLyrics()->getLyrics()
    );

    for($i = 0; $i < 4; $i++)
    {
      $this->writeInt($lyricTrack === null ? 0 : 1);
      $this->writeStringInteger('');
    }
  }

  private function writeMarker(Marker $marker)
  {
    $this->writeStringByteSizeOfInteger($marker->getTitle());
    $this->writeColor($marker->getColor());
  }

  private function writeMeasure(Measure $measure, $changeTempo)
  {
    for($v = 0; $v < 2; $v++)
    {
      $voices = array();

      for($m = 0; $m < $measure->countBeats(); $m++)
      {
        $beat = $measure->getBeat($m);
        if($v < $beat->countVoices())
        {
          $voice = $beat->getVoice($v);
          if(!$voice->isEmpty())
          {
            $voices[] = $voice;
          }
        }
      }

      if(count($voices) > 0)
      {
        $this->writeInt(count($voices));
        for($i = 0; $i < count($voices); $i++)
        {
          $voice = $voices[$i];
          $this->writeBeat($voice, $voice->getBeat(), $measure, $changeTempo && $i == 0);					
        }
      }
      else
      {
        $count = $measure->getTimeSignature()->getNumerator();
        $beat = new Beat();

        if($v < $beat->countVoices())
        {
          $voice = $beat->getVoice($v);
          $voice->getDuration()->setValue($measure->getTimeSignature()->getDenominator()->getValue());
          $voice->setEmpty(true);

          $this->writeInt($count);
          for($i = 0; $i < $count; $i++)
          {
            $this->writeBeat($voice, $voice->getBeat(), $measure, $changeTempo && $i == 0);
          }
        }
      }
    }
  }

  private function writeMeasureHeader(MeasureHeader $measure, TimeSignature $timeSignature)
  {
    $flags = 0;

    if($measure->getNumber() == 1)
    {
      $flags |= 0x40;
    }

    if($measure->getNumber() == 1 || !$measure->getTimeSignature()->isEqual($timeSignature))
    {
      $flags |= 0x01;
      $flags |= 0x02;
    }

    if($measure->isRepeatOpen())
    {
      $flags |= 0x04;
    }

    if($measure->getRepeatClose() > 0)
    {
      $flags |= 0x08;
    }

    if($measure->getRepeatAlternative() > 0)
    {
      $flags |= 0x10;
    }

    if($measure->hasMarker())
    {
      $flags |= 0x20;
    }

    $this->writeUnsignedByte($flags);

    if(($flags & 0x01) != 0)
    {
      $this->writeByte($measure->getTimeSignature()->getNumerator());
    }

    if(($flags & 0x02) != 0)
    {
      $this->writeByte($measure->getTimeSignature()->getDenominator()->getValue());
    }

    if(($flags & 0x08) != 0)
    {
      $this->writeByte($measure->getRepeatClose() + 1);
    }

    if(($flags & 0x20) != 0)
    {
      $this->writeMarker($measure->getMarker());
    }

    if(($flags & 0x10) != 0)
    {
      $this->writeByte($measure->getRepeatAlternative());
    }

    if(($flags & 0x40) != 0)
    {
      $this->skipBytes(2);
    }

    if(($flags & 0x01) != 0)
    {
      $this->writeBytes( $this->makeEighthNoteBytes( $measure->getTimeSignature() ));
    }

    if(($flags & 0x10) == 0)
    {
      $this->writeByte(0);
    }

    if($measure->getTripletFeel() == MeasureHeader::TRIPLET_FEEL_NONE)
    {
      $this->writeByte(0);
    }
    else if($measure->getTripletFeel() == MeasureHeader::TRIPLET_FEEL_EIGHTH)
    {
      $this->writeByte(1);
    }
    else if($measure->getTripletFeel() == MeasureHeader::TRIPLET_FEEL_SIXTEENTH)
    {
      $this->writeByte(2);
    }
  }

  private function writeMeasureHeaders(Song $song)
  {
    $timeSignature = new TimeSignature();

    if($song->countMeasureHeaders() > 0)
    {
      for($i = 0; $i < $song->countMeasureHeaders(); $i++)
      {
        if($i > 0)
        {
          $this->skipBytes(1);
        }
        $header = $song->getMeasureHeader($i);
        $this->writeMeasureHeader($header, $timeSignature);
        $timeSignature->setNumerator($header->getTimeSignature()->getNumerator());
        $timeSignature->getDenominator()->setValue(
          $header->getTimeSignature()->getDenominator()->getValue()
        );
      }
    }
  }

  private function writeMeasures(Song $song, Tempo $tempo)
  {
    for($i = 0; $i < $song->countMeasureHeaders(); $i++)
    {
      $header = $song->getMeasureHeader($i);

      for($j = 0; $j < $song->countTracks(); $j++)
      {
        $track = $song->getTrack($j);
        $measure = $track->getMeasure($i);
        $this->writeMeasure($measure, $header->getTempo()->getValue() != $tempo->getValue());
        $this->skipBytes(1);
      }

      $tempo->copyFrom($header->getTempo());
    }
  }

  private function writeMixChange(Tempo $tempo)
  {
    for($i = 0; $i < 23; $i++)
    {
      $this->writeByte(0xff);
    }

    $this->writeStringByteSizeOfInteger('');
    $this->writeInt($tempo !== null ? $tempo->getValue() : -1);

    if($tempo !== null)
    {
      $this->skipBytes(1);
    }

    $this->writeByte(0x01);
    $this->writeByte(0xff);
  }

  private function writeNote(Note $note)
  {
    $flags = 0x20 | 0x10;

    if($note->getEffect()->isVibrato()
        || $note->getEffect()->isBend()
        || $note->getEffect()->isGrace() 
        || $note->getEffect()->isSlide()
        || $note->getEffect()->isHammer()
        || $note->getEffect()->isLetRing()
        || $note->getEffect()->isPalmMute()
        || $note->getEffect()->isStaccato()
        || $note->getEffect()->isHarmonic()
        || $note->getEffect()->isTrill()
        || $note->getEffect()->isTremoloPicking() )
    {
      $flags |= 0x08;
    }

    if($note->getEffect()->isGhostNote())
    {
      $flags |= 0x04;
    }

    if($note->getEffect()->isHeavyAccentuatedNote())
    {
      $flags |= 0x02;
    }

    if($note->getEffect()->isAccentuatedNote())
    {
      $flags |= 0x40;
    }

    $this->writeUnsignedByte($flags);

    if(($flags & 0x20) != 0)
    {
      $typeHeader = 0x01;
      if($note->isTiedNote())
      {
        $typeHeader = 0x02;
      }
      else if($note->getEffect()->isDeadNote())
      {
        $typeHeader = 0x03;
      }
      $this->writeUnsignedByte($typeHeader);
    }

    if(($flags & 0x10) != 0)
    {
      $this->writeByte(intval((($note->getVelocity() - Velocities::MIN_VELOCITY) / Velocities::VELOCITY_INCREMENT) + 1));
    }

    if(($flags & 0x20) != 0)
    {
      $this->writeByte($note->getValue());
    }

    $this->skipBytes(1);

    if(($flags & 0x08) != 0)
    {
      $this->writeNoteEffects($note->getEffect());
    }
  }

  private function writeNoteEffects(NoteEffect $effect)
  {
    $flags1 = 0;
    $flags2 = 0;

    if($effect->isBend())
    {
      $flags1 |= 0x01;
    }

    if($effect->isHammer())
    {
      $flags1 |= 0x02;
    }

    if($effect->isLetRing())
    {
      $flags1 |= 0x08;
    }

    if($effect->isGrace())
    {
      $flags1 |= 0x10;
    }

    if($effect->isStaccato())
    {
      $flags2 |= 0x01;
    }

    if($effect->isPalmMute())
    {
      $flags2 |= 0x02;
    }

    if($effect->isTremoloPicking())
    {
      $flags2 |= 0x04;
    }

    if($effect->isSlide())
    {
      $flags2 |= 0x08;
    }

    if($effect->isHarmonic())
    {
      $flags2 |= 0x10;
    }

    if($effect->isTrill())
    {
      $flags2 |= 0x20;
    }

    if($effect->isVibrato())
    {
      $flags2 |= 0x40;
    }
    $this->writeUnsignedByte($flags1);
    $this->writeUnsignedByte($flags2);

    if(($flags1 & 0x01) != 0)
    {
      $this->writeBend($effect->getBend());
    }

    if(($flags1 & 0x10) != 0)
    {
      $this->writeGrace($effect->getGrace());
    }

    if(($flags2 & 0x04) != 0)
    {
      $this->writeTremoloPicking($effect->getTremoloPicking());
    }

    if(($flags2 & 0x08) != 0)
    {
      $this->writeByte(1);
    }

    if (($flags2 & 0x10) != 0)
    {
      $this->writeByte(1);
    }

    if (($flags2 & 0x20) != 0)
    {
      $this->writeTrill($effect->getTrill());
    }
  }

  private function makeEighthNoteBytes(TimeSignature $timeSignature)
  {
    $bytes = array(0,0,0,0);

    if($timeSignature->getDenominator()->getValue() <= Duration::EIGHTH)
    {
      $eighthsInDenominator = intval(Duration::EIGHTH / $timeSignature->getDenominator()->getValue());
      $total = ($eighthsInDenominator * $timeSignature->getNumerator());
      $byteValue = intval( $total / 4 );
      $missingValue = $total - (4 * $byteValue);

      for($i = 0 ; $i < count($bytes); $i++)
      {
        $bytes[$i] = $byteValue;
      }

      if($missingValue > 0)
      {
        $bytes[0] += $missingValue;
      }
    }

    return $bytes;
  }

  private function toChannelByte($short)
  {
    return intval(($short + 1) / 8);
  }

  private function toCommentLines($comments)
  {
    $lines = array();

    $line = $comments;

    while(strlen($line) > 127)
    {
      $subline = substr($line, 0, 127);
      $lines[] = $subline;
      $line = substr($line, 127);
    }

    $lines[] = $line;

    return $lines;
  }

  private function toStrokeValue(Stroke $stroke)
  {
    if($stroke->getValue() == Duration::SIXTY_FOURTH)
    {
      return 2;
    }
    if($stroke->getValue() == Duration::THIRTY_SECOND)
    {
      return 3;
    }
    if($stroke->getValue() == Duration::SIXTEENTH)
    {
      return 4;
    }
    if($stroke->getValue() == Duration::EIGHTH)
    {
      return 5;
    }
    if($stroke->getValue() == Duration::QUARTER)
    {
      return 6;
    }

    return 2;
  }

  private function writeSetup()
  {
    $this->writeInt( 210 );
    $this->writeInt( 297 );
    $this->writeInt( 10 );
    $this->writeInt( 10 );
    $this->writeInt( 15 );
    $this->writeInt( 10 );
    $this->writeInt( 100 );

    $this->writeByte(255);
    $this->writeByte(1);

    for($i = 0; $i < count($this->setUpLines); $i++)
    {
      $this->writeInt(strlen($this->setUpLines[$i]) + 1);
      $this->writeStringByte($this->setUpLines[$i], 0);
    }
  }

  private function writeText(Text $text)
  {
    $this->writeStringByteSizeOfInteger($text->getValue());
  }

  private function writeTrack(Track $track)
  {
    $channel = $this->getChannelRoute($track->getChannelId());

    $flags = 0;
    if($this->isPercussionChannel($track->getSong(), $track->getChannelId()))
    {
      $flags |= 0x01;
    }

    $this->writeUnsignedByte($flags);
    $this->writeUnsignedByte(8 | $flags);
    $this->writeStringByte($track->getName(), 40);
    $this->writeInt(count($track->getStrings()));

    for($i = 0; $i < 7; $i++)
    {
      $value = 0;
      if(count($track->getStrings()) > $i)
      {
        $string = $track->getStrings()[$i];
        $value = $string->getValue();
      }
      $this->writeInt($value);
    }

    $this->writeInt(1);
    $this->writeInt($channel->getChannel1() + 1);
    $this->writeInt($channel->getChannel2() + 1);
    $this->writeInt(24);
    $this->writeInt($track->getOffset());
    $this->writeColor($track->getColor());
    $this->writeBytes(
        array(
          67, 1, 0, 0,
          0, 0, 0, 0,
          0, 0, 0, 0,
          0, 100, 0, 0,
          0, 1, 2, 3,
          4, 5, 6, 7,
          8, 9, 10, -1,
          3, -1, -1, -1,
          -1, -1, -1, -1,
          -1, -1, -1, -1,
          -1, -1, -1, -1
        )
    );
  }

  private function writeTracks(Song $song)
  {
    for($i = 0; $i < $song->countTracks(); $i++)
    {
      $track = $song->getTrack($i);
      $this->writeTrack($track);
    }
  }

  private function writeTremoloBar(EffectTremoloBar $effect)
  {
    $points = count($effect->getPoints());
    $this->writeByte(1);
    $this->writeInt(0);
    $this->writeInt($points);
    for($i = 0; $i < $points; $i++)
    {
      $point = $effect->getPoints()[$i];
      $this->writeInt($point->getPosition() * GprInterface::GP_BEND_POSITION / EffectBend::MAX_POSITION_LENGTH);
      $this->writeInt($point->getValue() * GprInterface::GP_BEND_SEMITONE * 2);
      $this->writeByte(0);
    }
  }
	
  private function writeTremoloPicking(EffectTremoloPicking $effect)
  {
    if($effect->getDuration()->getValue() == Duration::EIGHTH)
    {
      $this->writeByte(1);
    }
    else if($effect->getDuration()->getValue() == Duration::SIXTEENTH)
    {
      $this->writeByte(2);
    }
    else if($effect->getDuration()->getValue() == Duration::THIRTY_SECOND)
    {
      $this->writeByte(3);
    }
  }

  private function writeTrill(EffectTrill $trill)
  {
    $this->writeByte($trill->getFret());

    if($trill->getDuration()->getValue() == Duration::SIXTEENTH)
    {
      $this->writeByte(1);
    }
    else if($trill->getDuration()->getValue() == Duration::THIRTY_SECOND)
    {
      $this->writeByte(2);
    }
    else if($trill->getDuration()->getValue() == Duration::SIXTY_FOURTH)
    {
      $this->writeByte(3);
    }
  }
}
