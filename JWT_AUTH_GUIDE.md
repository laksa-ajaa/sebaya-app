# Panduan JWT Authentication

## 1. Login untuk Mendapatkan Token

### Request
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

### Response
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        ...
    }
}
```

## 2. Menggunakan Token di Request

Setelah mendapatkan `access_token` dari response login, gunakan token tersebut di header setiap request ke endpoint yang protected.

### Format Header
```http
Authorization: Bearer {access_token}
```

### Contoh Request dengan Token

#### cURL
```bash
curl -X POST http://your-domain/api/mood-check \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "mood_level": 4
  }'
```

#### JavaScript (Fetch API)
```javascript
const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

fetch('http://your-domain/api/mood-check', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    mood_level: 4
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

#### JavaScript (Axios)
```javascript
const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

axios.post('http://your-domain/api/mood-check', {
  mood_level: 4
}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
.then(response => console.log(response.data))
.catch(error => console.error(error));
```

#### PHP (Guzzle)
```php
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

$client = new \GuzzleHttp\Client();
$response = $client->post('http://your-domain/api/mood-check', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'mood_level' => 4
    ]
]);

$data = json_decode($response->getBody(), true);
```

#### Python (Requests)
```python
import requests

token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

data = {
    'mood_level': 4
}

response = requests.post(
    'http://your-domain/api/mood-check',
    headers=headers,
    json=data
)

print(response.json())
```

#### Postman
1. Pilih method dan URL (misal: `POST http://your-domain/api/mood-check`)
2. Buka tab **Headers**
3. Tambahkan header:
   - Key: `Authorization`
   - Value: `Bearer {your_token_here}`
4. Tambahkan header:
   - Key: `Content-Type`
   - Value: `application/json`
5. Buka tab **Body**, pilih **raw** dan **JSON**
6. Masukkan body request:
```json
{
    "mood_level": 4
}
```

#### Insomnia / Thunder Client
1. Pilih method dan URL
2. Di bagian **Auth** atau **Headers**, pilih **Bearer Token**
3. Masukkan token yang didapat dari login
4. Atau manual di Headers:
   - `Authorization: Bearer {token}`

## 3. Endpoint yang Memerlukan Authentication

Semua endpoint di bawah ini memerlukan JWT token di header:

### Mood Check
- `POST /api/mood-check` - Check mood hari ini
- `GET /api/mood-check/today` - Get mood check hari ini
- `GET /api/mood-check/history` - Get history mood checks

### Journal
- `POST /api/journal` - Simpan jurnal
- `GET /api/journal/today` - Get jurnal hari ini
- `GET /api/journal/history` - Get history jurnal

### User Info
- `GET /api/user` - Get info user yang sedang login

## 4. Error Response

### Token Tidak Ada / Invalid
```json
{
    "message": "Unauthenticated."
}
```
Status: `401 Unauthorized`

### Token Expired
```json
{
    "message": "Token has expired"
}
```
Status: `401 Unauthorized`

### Token Invalid
```json
{
    "message": "Token Signature could not be verified."
}
```
Status: `401 Unauthorized`

## 5. Refresh Token (Jika Diperlukan)

Jika token sudah expired, user perlu login ulang untuk mendapatkan token baru.

## 6. Tips

1. **Simpan token dengan aman** - Jangan hardcode token di frontend, gunakan secure storage
2. **Handle token expiration** - Implementasi logic untuk auto-login ulang saat token expired
3. **Gunakan HTTPS** - Selalu gunakan HTTPS di production untuk keamanan token
4. **Token expiry** - Default token berlaku selama 60 menit (dapat diubah di `.env` dengan `JWT_TTL`)

## 7. Contoh Flow Lengkap

```javascript
// 1. Login
const loginResponse = await fetch('http://your-domain/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

const { access_token } = await loginResponse.json();

// 2. Simpan token (contoh: localStorage)
localStorage.setItem('token', access_token);

// 3. Gunakan token untuk request protected
const token = localStorage.getItem('token');

const moodCheckResponse = await fetch('http://your-domain/api/mood-check', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ mood_level: 4 })
});

const moodData = await moodCheckResponse.json();
console.log(moodData);
```

