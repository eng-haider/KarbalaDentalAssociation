import React from 'react';
import { Composition, registerRoot } from 'remotion';
import { LaunchVideo } from './LaunchVideo';
import { FPS, FRAMES } from '../timing.mjs';

registerRoot(() => <Composition id="KarbalaLaunch" component={LaunchVideo} width={1080} height={1920} fps={FPS} durationInFrames={FRAMES} />);
