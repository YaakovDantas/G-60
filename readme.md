# Parasite Patern

![parasite pattern example](assets/parasite.png)

## Configuration:

1° Clone this repository

`git clone !!`

2° Install dependecies

`composer install`

3° For OpenCV for php:

`https://github.com/php-opencv/php-opencv/wiki/Installation`

4° For CloudMersive, create an account https://account.cloudmersive.com/login

4.1° Create an API_KEY in https://account.cloudmersive.com/keys

`CLOUDMERSIVE_API_KEY=<CLOUDMERSIVE_API_KEY>`

## Parameters

| Option Nam  | Description                                                          | Required | Default      | Options               |
| ----------- | ---------------------------------                                    |----------| -----------  | ------------------    |
| --option    | API's to create patern                                               | true     | opencv       | opencv - cloudmersive |
| --parasite  | Set only black or random black and white                             | false    | false        | true - false          |
| --img       | Name of file.ext in the images folder                                | false    | off          | on - off              |
| --color     | Random and colorfull parasite pattern, parasite option must be false | false    | off          | on - off              |

## Set your images to create the pattern in the `images` folder

## The new images will be set in the `results` folder

## Examples (`php index.php --option=opencv|cloudmersive --parasite=true|false --img=my_image.png`)

##### It will run process image with opencv and set the black label
`php index.php --img=my_image.png --option=opencv --parasite=false`

##### It will run process image with opencv and alternate between black and white label
`php index.php --img=my_image.png --option=opencv --parasite=true`

![parasite example](assets/parasite2.png)

##### It will run process image with cloudmersive API and alternate between black and white label
`php index.php --img=my_image.png --option=cloudmersive --parasite=true`

##### It will run process image with cloudmersive API and alternate between RGB colors for label
`php index.php --img=my_image.png --option=cloudmersive --color=true`

![parasite colorfull example](assets/colorfull.png)
