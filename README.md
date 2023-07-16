<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://smileitsolutions.uk/_nuxt/SmileITSolutionsMainLogoWhiteBG.398f0df7.png" width="400" alt="Laravel Logo"></a></p>

# Laravel Bank API for a Fake Financial Institution

This project involves building an internal API for a fake financial institution using PHP and Laravel. The API should allow bank employees to perform basic banking functions such as creating new bank accounts for customers, transferring amounts between accounts, retrieving balances for accounts, and retrieving transfer history for accounts.

## Tasks

The following tasks must be implemented using PHP and Laravel:

1. Create API routes that allow bank employees to create new bank accounts for customers, with an initial deposit amount. A single customer may have multiple bank accounts.
2. Create API routes that allow bank employees to transfer amounts between any two accounts, including those owned by different customers.
3. Create API routes that allow bank employees to retrieve balances for accounts.
4. Create API routes that allow bank employees to retrieve transfer history for accounts.
5. Write tests for your business logic to ensure that the API functions correctly.

## Installation

To install the project, follow these steps:

1. Clone the repository using bash (or download the ZIP file):

```
git clone https://github.com/mmdshry/smile-it-bank-api
```

2. Navigate to the project directory:

```
cd smile-it-bank-api
```

3. Install the dependencies:

```
composer install
```

4. Create a copy of the `.env.example` file and rename it to `.env`. Update the necessary database configuration in the `.env` file.

5. Generate an application key:

```
php artisan key:generate
```

6. Run the database migrations and seed the database with sample customers:

```
php artisan migrate --seed
```

7. Run the project:

```
php artisan serve
```

## Running the Tests

To run the tests, execute the following command:

```
php artisan test
```

The tests will ensure that the application's business logic and functionality are working as expected.

## API Routes

The following API routes are available:

- `POST /api/v1/accounts` - Create a new bank account for a customer.
- `GET /api/v1/accounts/{account}/balance` - Get the balance for a specific account.
- `POST /api/v1/accounts/transfers` - Transfer amounts between accounts.
- `GET /api/v1/accounts/{account}/transfers` - Get the transfer history for a specific account.

## API Documentation

### Endpoints

#### `POST /api/accounts`

Creates a new bank account for a customer.

**Request Body**

| Parameter | Type    | Description                     |
| --------- | ------- | ------------------------------- |
| `customer_id` | `integer` | The ID of the customer. |
| `balance` | `number` | (Optional) The initial deposit amount. |

**Response**

- `201 Created`: The API responds with a JSON object containing the newly created account details, including the account ID, customer ID, current balance, and timestamps.
- `422 Unprocessable Entity`: If the request data is invalid or missing required fields, the API responds with an error message specifying the issue.

#### `GET /api/v1/accounts/{account}/balance`

Retrieves the balance for a specific bank account.

**Path Parameters**

| Parameter | Description |
| --------- | ----------- |
| `account_id` | The ID of the account. |

**Response**

- `200 OK`: The API responds with a JSON object containing the current balance of the account.
- `404 Not Found`: If the account ID does not exist, the API responds with an error message.

#### `POST /api/v1/accounts/transfers`

Transfers a specified amount from one bank account to another.

**Request Body**

| Parameter | Type    | Description                     |
| --------- | ------- | ------------------------------- |
| `source_account_id` | `integer` | The ID of the source account. |
| `destination_account_id` | `integer` | The ID of the destination account. |
| `amount` | `number` | The amount to transfer. |

**Response**

- `201 Created`: If the transfer is successful, the API responds with a JSON object containing a success message and the transfer record.
- `422 Unprocessable Entity`: If the source account has insufficient balance for the transfer, or the request data is invalid, the API responds with an error message.

#### `GET /api/v1/accounts/{account}/transfers`

Retrieves the transfer history for a specific bank account.

**Path Parameters**

| Parameter | Description |
| --------- | ----------- |
| `account_id` | The ID of the account. |

**Response**

- `200 OK`: The API responds with a JSON array containing the transfer history for the specified account divided into two parts (incoming and outgoing). Each transfer record includes details such as the transfer ID, source account ID, destination account ID, and transfer amount.
- `404 Not Found`: If the account ID does not exist, the API responds with an error message.
