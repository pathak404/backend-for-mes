
# Backend for Mes

![PHP >= 8.0](https://img.shields.io/badge/PHP-%3E%3D8.0-787CB5)
![php-jwt >= 6.3](https://img.shields.io/badge/JWT-%3E%3D6.3-fb015b)
![dotenv >= 6.8.0](https://img.shields.io/badge/dot--env-%3E%3D5.4-blueviolet)
![composer >= 2.3.10](https://img.shields.io/badge/composer-2.3.10-brown)
![Build](https://img.shields.io/badge/test-pass-brightgreen)

A custom PHP Framework with many features and minimal dependencies.
This project provides backend support to [Mes Management System](https://github.com/2bytecoder/mes-management-system). 






## Features
- Custom PHP framework based on MVC pattern.
- JSON payloads
- GET, POST, PUT, DELETE Methods supported
- CORS enabled
- Supports MySQL database
## Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`DB_DSN` = mysql:host=localhost;port=3306;dbname=DATABASE_NAME\
`DB_USERNAME` = YOUR_DATABASE_USERNAME\
`DB_PASSWORD` = YOUR_DATABASE_PASSWORD\
`JWT_SECRET` = YOUR_SECRET_KEY_PHRASE


## Run Locally

Clone the project

```bash
  git clone https://github.com/2bytecoder/backend-for-mes
```

Go to the project directory

```bash
  cd backend-for-mes
```

Install dependencies

```bash
  composer install
```

Dump migration
```bash
  php migrations.php
```

Start the server

```bash
  cd public
  php -S localhost:8080
```



## API Reference

### Admin Account

#### Admin login

```http
  GET /auth
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `phone` | `number` | **Required**. Registered 10 digit phone number |
| `password` | `string` | **Required**. Account password |



#### Create Admin Account
```http
  POST /admin
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `phone` | `number` | **Required**. New phone number |
| `email` | `string` | **Required**. New email address |
| `password` | `string` | **Required**. Account password |



#### Get account information

```http
  GET /admin
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|



#### Update account information

```http
  PUT /admin
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `admin_id` | `number` | **Required**. Admin ID |
| `full_name` | `string` | **Optional**. Full Name |
| `phone` | `number` | **Optional**. 10 digit phone number |
| `email` | `string` | **Optional**. New email address |
| `password` | `string` | **Optional**. New Password |


#### Delete admin account

```http
  DELETE /admin
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `admin_id` | `number` | **Required**. Admin ID |






### Student Account


#### Create student account

```http
  POST /student
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `full_name` | `string` | **Required**. Student Name |
| `phone` | `number` | **Required**. Student's phone number |
| `father_name` | `string` | **Required**. Student's father name |
| `year` | `number` | **Required**. Passout year |
| `branch` | `string` | **Required**. CSE \| CIVIL \| EEE \| OTHERS |
| `meal_type` | `string` | **Required**. ALL \| BL \| BD \| LD |




#### Get account information

```http
  GET /student
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `find_by` | `number` | **Required**. Any entity |
| `entity` | `string\|number` | **Required**. Entity value |


#### Update account information

```http
  PUT /student
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `find_by` | `number` | **Required**. Any entity |
| `entity` | `string\|number` | **Required**. Entity value |

ANY

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `full_name` | `string` | **Optional**. Student Name |
| `phone` | `number` | **Optional**. Student's phone number |
| `father_name` | `string` | **Optional**. Student's father name |
| `year` | `number` | **Optional**. Passout year |
| `branch` | `string` | **Optional**. CSE \| CIVIL \| EEE \| OTHERS |
| `meal_type` | `string` | **Optional**. ALL \| BL \| BD \| LD |






#### Delete student account

```http
  DELETE /student
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `find_by` | `number` | **Required**. Any entity |
| `entity` | `string\|number` | **Required**. Entity value |


### All student records

```http
  GET /student_all
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `limit` | `number` | **Required**.  No. of results to retrieve |




### Student Attendance

```http
  GET /attendance
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |

ANY

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `date` | `string` | **Optional**.  YYYY-MM-DD|
| `month` | `number` | **Optional**.  Any between 1-12|
| `year` | `number` | **Optional**.  YYYY|

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `month` | `number` | **Required**.  Any between 1-12|
| `year` | `number` | **Required**.  YYYY|



### Wallet 

#### Wallet information

```http
  GET /wallet
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |


#### All student wallet records

```http
  GET /wallet_all
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|



### Transactions

#### Create new transaction for student
```http
  POST /transaction
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID (For student)|
| `txn_amount` | `int\|float` | **Required**. Transaction amount|
| `txn_type` | `string` | **Required**. withdraw \| subscription|
| `txn_desc` | `string` | **Required**. Transaction Description|
| `payment_method` | `string` | **Required**. Payment Method|
| `txn_status ` | `string` | **Required**. pending \| failed \| success (set to success if withdraw)|




#### All Transactions of specific Student / Unregular Customer

```http
  GET /transaction
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID (For student)|

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `phone` | `number` | **Required**. Phone number (For Unregular Customer) |



#### All Transactions


```http
  GET /transaction_all
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `limit` | `number` | **Required**. No. of results to retrieve |




### Order

#### Create Order


```http
  POST /order
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

For Student 

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `student_id` | `number` | **Required**. Student ID |
| `order_type` | `string` | **Required**. breakfast \| lunch \| dinner |
| `customer` | `string` | **Required**. regular |
| `payment_method` | `string` | **Required**. wallet |
| `order_date` | `string` | **Optional**. YYYY-MM-DD |

For Unregular Customer

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `order_type` | `string` | **Required**. B \| LV \| LNV \| DV \| DNV \| FDV \| FDNV |
| `customer` | `string` | **Required**. 10 digit phone no. |
| `payment_method` | `string` | **Required**. cash \| upi |
| `order_date` | `string` | **Optional**. YYYY-MM-DD |


#### View order 

```http
  GET /order
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|


| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `customer` | `number` | **Required**. Student ID \| Phone no.|
| `service_date` | `string` | **Optional**. YYYY-MM-DD |

OR

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `order_id` | `number` | **Required**. Order ID |
| `service_date` | `string` | **Optional**. YYYY-MM-DD |




#### All Orders

```http
  GET /order_all
```

| Header | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Authorization`      | `Bearer` | **Required**. JWT Token|

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `limit` | `number` | **Required**. No. of results to retrieve |

