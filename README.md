# Dynamic-noise-data-smoothing

## Introduction

The function ``RawDataSmoothing(...)`` receives an array of data and performs several operations in order to smooth the data, also it keeps un-smoothed some vital break zones used to determine important events.

## Variables

``function RawDataSmoothing ( array $raw_data, $percentage = 20, $breakingDetection = 20, $breakingDetectionCalibration = 100)``

**@(array) $raw_data**

The array of raw data that is going to be smoothed by the function. It must be a 1 level array of (int) or (float) data with numeric keys.

**@(int) $percentage**

This value determines the smooth of the result, higher values will “soft” the data more. By default is set to 20. In some really unstable $raw_data, you will need to change this percentage to a higher value, normally it is enought to set it up to 40%.

**@(int) $breakingDetection**

This value is used to determine those valuable “high peaks” and protect them from the smoothing process. The amount of $breakingDetection will determine the “y-axis” points (height) of the $raw_data that the function will avoid to smooth.

![Image of Breaking Detection](http://i.imgur.com/Tpcp6Y6.jpg)

In the above image, you can see that there is a “vital breaking point” that will be usefull not to smooth. In this case, leaving $breakingDetection var as its default (20) will be enought to smooth properly all the $raw_data and keeping that “vital breaking point” untouched. 

The rest of the noise that you can see at that graph, have a “height” between 3 to 6 points so the $breakingDetection var will let the function to smooth all those noisy points. 

If you have a $raw_data with constant high peaks of noise, it will be a good idea to increase the $breakingDetection var in order to smooth properly the $raw_data. 

Keep in mind, that the “high peaks” that are puntuals or not continue along the “x-axis” will not be selected by the $breakingDetection var as untouchables. An example of this case can be those $raw_datas with “peaks” that goes down to zero in some points and return to the average of the function

**@(int) $breakingDetectionCalibration**

This variable is used to determine the range of data to be used by the function in order to smooth the selected point that is being procesed at the $raw_data. This variable is set to 100 by default and it is not needed to change its value. Increasing this data will fairly help the smoothing process as it is almost only used to protect the function from array segmentation.

## Usage

+ Default raw data smooth with default variables used:

``$smoothed_result = RawDataSmoothing( $raw_data );``

+ Raw data smooth with a custom percentage:

``$smoothed_result = RawDataSmoothing( $raw_data, 30);``

+ Raw data smooth with a custom percentage and custom breaking detection:

``$smoothed_result = RawDataSmoothing( $raw_data, 30, 25);``

+ Raw data smooth with custom breaking detection calibration:

``$smoothed_result = RawDataSmoothing( $raw_data, 20, 20, 150);``


