import React from 'react';
import { Composition, registerRoot } from 'remotion';
import { CompanyOffersVideo } from './CompanyOffersVideo';
import { OFFERS_FPS, OFFERS_FRAMES } from '../company-offers-timing.mjs';

registerRoot(() => <Composition id="KarbalaCompanyOffers" component={CompanyOffersVideo} width={1080} height={1920} fps={OFFERS_FPS} durationInFrames={OFFERS_FRAMES} />);
