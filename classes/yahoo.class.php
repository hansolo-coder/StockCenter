<?php
	/**
	 * fetches available data from the yahoo csv web service
	 */
	class yahoo
	{
		/*
		 Yahoo Codes and array locations:
		 [0]  = a  = ask
		 [1]  = b2 = ask realtime
		 [2]  = b  = bid
		 [3]  = b3 = bid realtime
		 [4]  = p  = previous close
		 [5]  = o  = open
		 [6]  = y  = dividend yield
		 [7]  = d  = dividend per share
		 [8]  = r1 = dividend pay date
		 [9]  = q  = ex dividend date
		 [10] = c1 = change
		 [11] = c  = change and percent change
		 [12] = c6 = change realtime
		 [13] = k2 = change percent realtime
		 [14] = p2 = change in percent
		 [15] = c8 = after hours change realtime
		 [16] = c3 = commission
		 [17] = g  = days low
		 [18] = h  = days high
		 [19] = k1 = last trade with time realtime
		 [20] = l  = last trade with time
		 [21] = l1 = last trade price only
		 [22] = t8 = 1 year target price
		 [23] = m5 = change from 200 day moving average
		 [24] = m6 = change from 200 day moving average in percent
		 [25] = m7 = change from 50 day moving average
		 [26] = m8 = change from 50 day moving average in percent
		 [27] = m3 = 50 day moving average
		 [28] = m4 = 200 day moving average
		 [29] = w1 = the days value change
		 [30] = w4 = the days value change realtime
		 [31] = p1 = price paid
		 [32] = m  = the days range
		 [33] = m2 = the days range realtime
		 [34] = g1 = holdings gain percent
		 [35] = g3 = annualized gain
		 [36] = g4 = holdings gain
		 [37] = g5 = holdings gain percent realtime
		 [38] = g6 = holdings gain realtime
		 [39] = k  = 52 week high
		 [40] = j  = 52 week low
		 [41] = j5 = change from 52 week low
		 [42] = k4 = change from 52 week high
		 [43] = j6 = change from 52 week low in percent
		 [44] = k5 = change from 52 week high in percent
		 [45] = w  = 52 week range
		 [46] = v  = volume
		 [47] = j1 = market capitalization
		 [48] = j3 = market capitalization realtime
		 [49] = f6 = float shares
		 [50] = n  = name
		 [51] = n4 = notes
		 [52] = s  = symbol
		 [53] = s1 = shares owned
		 [54] = x  = stock exchange
		 [55] = j2 = shares outstanding
		 [56] = e  = earnings per share
		 [57] = e7 = earnings per share estimate current year
		 [58] = e8 = earnings per share estimate next year
		 [59] = e9 = earnings per share estimate next quarter
		 [60] = b4 = book value
		 [61] = j4 = EBITDA
		 [62] = p5 = price/sales
		 [63] = p6 = price/book
		 [64] = r  = p/e ratio
		 [65] = r2 = p/e ratio realtime
		 [66] = r5 = peg ratio
		 [67] = r6 = price/eps estimate current year
		 [68] = r7 = price/eps estimate next year
		 [69] = s7 = short ratio
		 [70] = t7 = ticker trend
		 [71] = t6 = trade links
		 [72] = s6 = revenue
		 */
	
		/**
		 * @var string
		 * @access private
		 * @return The base url for the yahoo web service
		 */
		private $url;
	
		/**
		 * @var string
		 * @access private
		 * @return the list of codes passed to retrieve specific information from the service
		 */
		private $codes;
	
		/**
		 * @var string
		 * @access public
		 * @return the ask price
		 */
		public $ask;
	
		/**
		 * @var string
		 * @access public
		 * @return the real time ask price
		 */
		public $askRealtime;
	
		/**
		 * @var string
		 * @access public
		 * @return the bid price
		 */
		public $bid;
	
		/**
		 * @var string
		 * @access public
		 * @return the real time bid price
		 */
		public $bidRealtime;
	
		/**
		 * @var string
		 * @access public
		 * @return the previous close price
		 */
		public $previousClose;
	
		/**
		 * @var string
		 * @access public
		 * @return the opening price
		 */
		public $open;
	
		/**
		 * @var string
		 * @access public
		 * @return the dividend yield
		 */
		public $dividendYield;
	
		/**
		 * @var string
		 * @access public
		 * @return the dividend per share
		 */
		public $dps;
	
		/**
		 * @var string
		 * @access public
		 * @return the dividend pay date
		 */
		public $dividendPayDate;
	
		/**
		 * @var string
		 * @access public
		 * @return the ex dividend date
		 */
		public $exDividendDate;
	
		/**
		 * @var string
		 * @access public
		 * @return the change amout for the stock
		 */
		public $change;
	
		/**
		 * @var string
		 * @access public
		 * @return the change and the percent of change
		 */
		public $changeAndPercent;
	
		/**
		 * @var string
		 * @access public
		 * @return the realtime change
		 */
		public $changeRealtime;
	
		/**
		 * @var string
		 * @access public
		 * @return the realtime change in percent
		 */
		public $changePercentRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return the change in percent
		 */
		public $changeInPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return after hours change realtime
		 */
		public $afterHoursChangeRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return commission
		 */
		public $commission;
		
		/**
		 * @var string
		 * @access public
		 * @return days low
		 */
		public $daysLow;
		
		/**
		 * @var string
		 * @access public
		 * @return days high
		 */
		public $daysHigh;
		
		/**
		 * @var string
		 * @access public
		 * @return last trade with time realtime
		 */
		public $lastTradeWithTimeRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return last trade with time
		 */
		public $lastTradeWithTime;
		
		/**
		 * @var string
		 * @access public
		 * @return last trade price only
		 */
		public $lastTradePriceOnly;
		
		/**
		 * @var string
		 * @access public
		 * @return 1 year target price
		 */
		public $oneYearTargetPrice;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 200 day moving average
		 */
		public $changeFrom200DayMovingAverage;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 200 day moving average in percent
		 */
		public $changeFrom200DayMovingAverageInPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 50 day moving average
		 */
		public $changeFrom50DayMovingAverage;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 50 day moving average in percent
		 */
		public $changeFrom50DayMovingAverageInPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return 50 day moving average
		 */
		public $fiftyDayMovingAverage;
		
		/**
		 * @var string
		 * @access public
		 * @return 200 day moving average
		 */
		public $twoHundredDayMovingAverage;
		
		/**
		 * @var string
		 * @access public
		 * @return the days value change
		 */
		public $theDaysValueChange;
		
		/**
		 * @var string
		 * @access public
		 * @return the days value change realtime
		 */
		public $theDaysValueChangeRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return price paid
		 */
		public $pricePaid;
		
		/**
		 * @var string
		 * @access public
		 * @return the days range
		 */
		public $theDaysRange;
		
		/**
		 * @var string
		 * @access public
		 * @return the days range realtime
		 */
		public $theDaysRangeRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return holdings gain percent
		 */
		public $holdingsGainPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return annualized gain
		 */
		public $annualizedGain;
		
		/**
		 * @var string
		 * @access public
		 * @return holdings gain
		 */
		public $holdingsGain;
		
		/**
		 * @var string
		 * @access public
		 * @return holdings gain percent realtime
		 */
		public $holdingsGainPercentRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return holdings gain realtime
		 */
		public $holdingsGainRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return 52 week high
		 */
		public $fiftyTwoWeekHigh;
		
		/**
		 * @var string
		 * @access public
		 * @return 52 week low
		 */
		public $fiftyTwoWeekLow;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 52 week low
		 */
		public $changeFrom52WeekLow;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 52 week high
		 */
		public $changeFrom52WeekHigh;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 52 week low in percent
		 */
		public $changeFrom52WeekLowInPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return change from 52 week high in percent
		 */
		public $changeFrom52WeekHighInPercent;
		
		/**
		 * @var string
		 * @access public
		 * @return 52 week range
		 */
		public $fiftyTwoWeekRange;
		
		/**
		 * @var string
		 * @access public
		 * @return volume
		 */
		public $volume;
		
		/**
		 * @var string
		 * @access public
		 * @return market capitalization
		 */
		public $marketCapitalization;
		
		/**
		 * @var string
		 * @access public
		 * @return market capitalization realtime
		 */
		public $marketCapitalizationRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return float shares
		 */
		public $floatShares;
		
		/**
		 * @var string
		 * @access public
		 * @return name
		 */
		public $name;
		
		/**
		 * @var string
		 * @access public
		 * @return notes
		 */
		public $notes;
		
		/**
		 * @var string
		 * @access public
		 * @return symbol
		 */
		public $symbol;
		
		/**
		 * @var string
		 * @access public
		 * @return shares owned
		 */
		public $sharesOwned;
		
		/**
		 * @var string
		 * @access public
		 * @return stock exchange
		 */
		public $stockExchange;
		
		/**
		 * @var string
		 * @access public
		 * @return shares outstanding
		 */
		public $sharesOutstanding;
		
		/**
		 * @var string
		 * @access public
		 * @return earnings per share
		 */
		public $earningsPerShare;
		
		/**
		 * @var string
		 * @access public
		 * @return earnings per share estimate current year
		 */
		public $earningsPerShareEstimateCurrentYear;
		
		/**
		 * @var string
		 * @access public
		 * @return earnings per share estimate next year
		 */
		public $earningsPerShareEstimateNextYear;
		
		/**
		 * @var string
		 * @access public
		 * @return earnings per share estimate next quarter
		 */
		public $earningsPerShareEstimateNextQuarter;
		
		/**
		 * @var string
		 * @access public
		 * @return book value
		 */
		public $bookValue;
		
		/**
		 * @var string
		 * @access public
		 * @return EBITDA
		 */
		public $ebitda;
		
		/**
		 * @var string
		 * @access public
		 * @return price/sales
		 */
		public $priceToSales;
		
		/**
		 * @var string
		 * @access public
		 * @return price/book
		 */
		public $priceToBook;
		
		/**
		 * @var string
		 * @access public
		 * @return p/e ratio
		 */
		public $peRatio;
		
		/**
		 * @var string
		 * @access public
		 * @return p/e ratio realtime
		 */
		public $peRatioRealtime;
		
		/**
		 * @var string
		 * @access public
		 * @return peg ratio
		 */
		public $pegRatio;
		
		/**
		 * @var string
		 * @access public
		 * @return price/eps estimate current year
		 */
		public $priceToEpsEstimateCurrentYear;
		
		/**
		 * @var string
		 * @access public
		 * @return price/eps estimate next year
		 */
		public $priceToEpsEstimateNextYear;
		
		/**
		 * @var string
		 * @access public
		 * @return short ratio
		 */
		public $shortRatio;
		
		/**
		 * @var string
		 * @access public
		 * @return ticker trend
		 */
		public $tickerTrend;
		
		/**
		 * @var string
		 * @access public
		 * @return trade links
		 */
		public $tradeLinks;
		
		/**
		 * @var string
		 * @access public
		 * @return revenue
		 */
		public $revenue;
		
		
		
		function __construct()
		{
			$this->url                                    = "http://finance.yahoo.com/d/quotes.csv?s=";
			$this->codes                                  = "&f=ab2bb3poydr1qc1cc6k2p2c8c3ghk1ll1t8m5m6m7m8m3m4w1w4p1mm2g1g3g4g5g6kjj5k4j6k5wvj1j3f6nn4ss1xj2ee7e8e9b4j4p5p6rr2r5r6r7s7t7t6s6";
			$this->symbol                                 = NULL;
			$this->ask                                    = NULL;
			$this->askRealtime                            = NULL;
			$this->bid                                    = NULL;
			$this->bidRealtime                            = NULL;
			$this->previousClose                          = NULL;
			$this->open                                   = NULL;
			$this->dividendYield                          = NULL;
			$this->dps                                    = NULL;
			$this->dividendPayDate                        = NULL;
			$this->exDividendDate                         = NULL;
			$this->change                                 = NULL;
			$this->changeAndPercent                       = NULL;
			$this->changeRealtime                         = NULL;
		 	$this->changePercentRealtime                  = NULL;
		    $this->changeInPercent                        = NULL;
		    $this->afterHoursChangeRealtime               = NULL;
		    $this->commission                             = NULL;
		    $this->daysLow                                = NULL;
		    $this->daysHigh                               = NULL;
		    $this->lastTradeWithTimeRealtime              = NULL;
		    $this->lastTradeWithTime                      = NULL;
		    $this->lastTradePriceOnly                     = NULL;
		    $this->oneYearTargetPrice                     = NULL;
		    $this->changeFrom200DayMovingAverage          = NULL;
		    $this->changeFrom200DayMovingAverageInPercent = NULL;
		    $this->changeFrom50DayMovingAverage           = NULL;
		    $this->changeFrom50DayMovingAverageInPercent  = NULL;
		    $this->fiftyDayMovingAverage                  = NULL;
		    $this->twoHundredDayMovingAverage             = NULL;
		    $this->theDaysValueChange                     = NULL;
		    $this->theDaysValueChangeRealtime             = NULL;
		    $this->pricePaid                              = NULL;
		    $this->theDaysRange                           = NULL;
		    $this->theDaysRangeRealtime                   = NULL;
		    $this->holdingsGainPercent                    = NULL;
		    $this->annualizedGain                         = NULL;
		    $this->holdingsGain                           = NULL;
		    $this->holdingsGainPercentRealtime            = NULL;
		    $this->holdingsGainRealtime                   = NULL;
		    $this->fiftyTwoWeekHigh                       = NULL;
		    $this->fiftyTwoWeekLow                        = NULL;
			$this->changeFrom52WeekLow                    = NULL;
			$this->changeFrom52WeekHigh                   = NULL;
			$this->changeFrom52WeekLowInPercent           = NULL;
			$this->changeFrom52WeekHighInPercent          = NULL;
			$this->fiftyTwoWeekRange                      = NULL;
			$this->volume                                 = NULL;
			$this->marketCapitalization                   = NULL;
			$this->marketCapitalizationRealtime           = NULL;
			$this->floatShares                            = NULL;
			$this->name                                   = NULL;
			$this->notes                                  = NULL;
			$this->symbol                                 = NULL;
			$this->sharesOwned                            = NULL;
			$this->stockExchange                          = NULL;
			$this->sharesOutstanding                      = NULL;
			$this->earningsPerShare                       = NULL;
			$this->earningsPerShareEstimateCurrentYear    = NULL;
			$this->earningsPerShareEstimateNextYear       = NULL;
			$this->earningsPerShareEstimateNextQuarter    = NULL;
			$this->bookValue                              = NULL;
			$this->ebitda                                 = NULL;
			$this->priceToSales                           = NULL;
			$this->priceToBook                            = NULL;
			$this->peRatio                                = NULL;
			$this->peRatioRealtime                        = NULL;
			$this->pegRatio                               = NULL;
			$this->priceToEpsEstimateCurrentYear          = NULL;
			$this->priceToEpsEstimateNextYear             = NULL;
			$this->shortRatio                             = NULL;
			$this->tickerTrend                            = NULL;
			$this->tradeLinks                             = NULL;
			$this->revenue                                = NULL;
		}
	
		
		/**
		 * @method getData()
		 * @return sets the values from the yahoo web service for the symbol into the class attributes
		 * @param requires symbol attribute to be set
		 */
		function getData()
		{
			if ($this->symbol != NULL)
			{
				$yData = file_get_contents($this->url . $this->symbol . $this->codes);
				$sData = explode(",", $yData);
	
				# ask
				$this->ask = $this->removeQuotes(trim($sData[0]));
	
				# ask realtime
				$this->askRealtime = $this->removeQuotes(trim($sData[1]));
	
				# bid
				$this->bid = $this->removeQuotes(trim($sData[2]));
	
				# bid realtime
				$this->bidRealtime = $this->removeQuotes(trim($sData[3]));
	
				# previous close
				$this->previousClose = $this->removeQuotes(trim($sData[4]));
	
				# open
				$this->open = $this->removeQuotes(trim($sData[5]));
	
				# dividend yield
				$this->dividendYield = $this->removeQuotes(trim($sData[6]));
					
				# dps
				$this->dps = $this->removeQuotes(trim($sData[7]));
	
				# dividend pay date
				$this->dividendPayDate = str_replace('"', '', $sData[8]);
	
				# ex dividend date
				$this->exDividendDate = str_replace('"', '', $sData[9]);
	
				# change
				$this->change = $this->removeQuotes(trim($sData[10]));
	
				# change and percent
				$this->changeAndPercent = str_replace('"', '', $sData[11]);
	
				# change realtime
				$this->change = $this->removeQuotes(trim($sData[12]));
	
				# change percent realtime
				$this->change = $this->removeQuotes(trim($sData[13]));
					
				# change in percent
				$this->changeInPercent = $this->removeQuotes(trim($sData[14]));
					
				# after hours change realtime
				$this->afterHoursChangeRealtime = $this->removeQuotes(trim($sData[15]));
					
				# commission
				$this->commission = $this->removeQuotes(trim($sData[16]));
					
				$this->daysLow = $this->removeQuotes(trim($sData[17]));
					
				# days high
				$this->daysHigh = $this->removeQuotes(trim($sData[18]));
					
				# last trade with time realtime
				$this->lastTradeWithTimeRealtime = $this->removeQuotes(trim($sData[19]));
					
				# last trade with time
				$this->lastTradeWithTime = $this->removeQuotes(trim($sData[20]));
					
				# last trade price only
				$this->lastTradePriceOnly = $this->removeQuotes(trim($sData[21]));
				
				# 1 year target price
				$this->oneYearTargetPrice = $this->removeQuotes(trim($sData[22]));
					
				# change from 200 day moving average
				$this->changeFrom200DayMovingAverage = $this->removeQuotes(trim($sData[23]));
					
				# change from 200 day moving average in percent
				$this->changeFrom200DayMovingAverageInPercent = $this->removeQuotes(trim($sData[24]));
				
				# change from 50 day moving average
				$this->changeFrom50DayMovingAverage = $this->removeQuotes(trim($sData[25]));
					
				# change from 50 day moving average in percent
				$this->changeFrom50DayMovingAverageInPercent = $this->removeQuotes(trim($sData[26]));
					
				# 50 day moving average
				$this->fiftyDayMovingAverage = $this->removeQuotes(trim($sData[27]));
					
				# 200 day moving average
				$this->twoHundredDayMovingAverage = $this->removeQuotes(trim($sData[28]));
					
				# the days value change
				$this->theDaysValueChange = $this->removeQuotes(trim($sData[29]));
					
				# the days value change realtime
				$this->theDaysValueChangeRealtime = $this->removeQuotes(trim($sData[30]));
					
				# price paid
				$this->pricePaid = $this->removeQuotes(trim($sData[31]));
					
				# the days range
				$this->theDaysRange = $this->removeQuotes(trim($sData[32]));
					
				# the days range realtime
				$this->theDaysRangeRealtime = $this->removeQuotes(trim($sData[33]));
					
				# holdings gain percent
				$this->holdingsGainPercent = $this->removeQuotes(trim($sData[34]));
					
				# annualized gain
				$this->annualizedGain = $this->removeQuotes(trim($sData[35]));
					
				# holdings gain
				$this->holdingsGain = $this->removeQuotes(trim($sData[36]));
					
				# holdings gain percent realtime
				$this->holdingsGainPercentRealtime = $this->removeQuotes(trim($sData[37]));
				
				# holdings gain realtime
				$this->holdingsGainRealtime = $this->removeQuotes(trim($sData[38]));
					
				# 52 week high
				$this->fiftyTwoWeekHigh = $this->removeQuotes(trim($sData[39]));
					
				# 52 week low
				$this->fiftyTwoWeekLow = $this->removeQuotes(trim($sData[40]));
					
				# change from 52 week low
				$this->changeFrom52WeekLow = $this->removeQuotes(trim($sData[41]));
					
				# change from 52 week high
				$this->changeFrom52WeekHigh = $this->removeQuotes(trim($sData[42]));
					
				# change from 52 week low in percent
				$this->changeFrom52WeekLowInPercent = $this->removeQuotes(trim($sData[43]));
				
				# change from 52 week high in percent
				$this->changeFrom52WeekHighInPercent = $this->removeQuotes(trim($sData[44]));
					
				# 52 week range
				$this->fiftyTwoWeekRange = $this->removeQuotes(trim($sData[45]));
					
				# volume
				$this->volume = $this->removeQuotes(trim($sData[46]));
					
				# market capitalization
				$this->marketCapitalization = $this->removeQuotes(trim($sData[47]));
					
				# market capitalization realtime
				$this->marketCapitalizationRealtime = $this->removeQuotes(trim($sData[48]));
					
				# float shares
				$this->floatShares = $this->removeQuotes(trim($sData[49]));
					
				# name
				$this->name = $this->removeQuotes(trim($sData[50]));
					
				# notes
				$this->notes = $this->removeQuotes(trim($sData[51]));
					
				# shares owned
				if(!is_numeric($sData[53]))
				{
					$this->sharesOwned = '0';
				}
				else
				{
					$this->sharesOwned = $this->removeQuotes(trim($sData[53]));
				}
					
				# stock exchange
				$this->stockExchange = $this->removeQuotes(trim($sData[54]));
					
				# shares outstanding
				$this->sharesOutstanding = $this->removeQuotes(trim($sData[55]));
				
				# earnings per share
				$this->earningsPerShare = $this->removeQuotes(trim($sData[56]));
					
				# earnings per share estimate current year
				$this->earningsPerShareEstimateCurrentYear = $this->removeQuotes(trim($sData[57]));
					
				# earnings per share estimate next year
				$this->earningsPerShareEstimateNextYear = $this->removeQuotes(trim($sData[58]));
					
				# earnings per share estimate next quarter
				$this->earningsPerShareEstimateNextQuarter = $this->removeQuotes(trim($sData[59]));
					
				# book value
				$this->bookValue = $this->removeQuotes(trim($sData[60]));
					
				# EBITDA
				$this->ebitda = $this->removeQuotes(trim($sData[61]));
					
				# price/sales
				$this->priceToSales = $this->removeQuotes(trim($sData[62]));
					
				# price/book
				$this->priceToBook = $this->removeQuotes(trim($sData[63]));
					
				# p/e ratio
				$this->peRatio = $this->removeQuotes(trim($sData[64]));
					
				# p/e ratio realtime
				$this->peRatioRealtime = $this->removeQuotes(trim($sData[65]));
					
				# peg ratio
				$this->pegRatio = $this->removeQuotes(trim($sData[66]));
					
				# price/eps estimate current year
				$this->priceToEpsEstimateCurrentYear = $this->removeQuotes(trim($sData[67]));
					
				# price/eps estimate next year
				$this->priceToEpsEstimateNextYear = $this->removeQuotes(trim($sData[68]));
					
				# short ratio
				$this->shortRatio = $this->removeQuotes(trim($sData[69]));
					
				# ticker trend
				$this->tickerTrend = $this->removeQuotes(trim($sData[70]));
					
				# trade links
				$this->tradeLinks = $this->removeQuotes(trim($sData[71]));
					
				# revenue
				$this->revenue = $this->removeQuotes(trim($sData[72]));
			}
		}

	
		function removeQuotes($value)
		{
			$newValue = str_replace('"', "", $value);
			return $newValue;
		}
	}
?>