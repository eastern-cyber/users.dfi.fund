// /api/users/route.js
import mysql from 'mysql2/promise';

// Remote MySQL configuration
const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  port: 3306,
  charset: 'utf8mb4'
};

// Validate that required env vars exist
const requiredEnvVars = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME'];
requiredEnvVars.forEach(envVar => {
  if (!process.env[envVar]) {
    throw new Error(`Missing required environment variable: ${envVar}`);
  }
});

async function getConnection() {
  const connection = await mysql.createConnection(dbConfig);
  return connection;
}

export async function GET(request) {
  let connection;
  
  try {
    connection = await getConnection();
    const { searchParams } = new URL(request.url);
    const email = searchParams.get('email');

    if (email) {
      const [users] = await connection.execute(
        'SELECT id, name, email, user_id, referrer_id, token_id, created_at, updated_at, plan_a FROM users WHERE email = ?',
        [email]
      );

      if (users.length === 0) {
        return new Response(JSON.stringify({
          success: false,
          message: 'User not found'
        }), {
          status: 404,
          headers: { 'Content-Type': 'application/json' }
        });
      }

      return new Response(JSON.stringify({
        success: true,
        data: users[0]
      }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' }
      });
    } else {
      const [users] = await connection.execute(
        'SELECT id, name, email, user_id, referrer_id, token_id, created_at, updated_at, plan_a FROM users LIMIT 100'
      );

      return new Response(JSON.stringify({
        success: true,
        data: users
      }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' }
      });
    }
  } catch (error) {
    console.error('Database error:', error);
    return new Response(JSON.stringify({
      success: false,
      message: 'Database error',
      error: error.message
    }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    });
  } finally {
    if (connection) await connection.end();
  }
}

export async function POST(request) {
  let connection;
  
  try {
    connection = await getConnection();
    const body = await request.json();
    const { email, password } = body;

    if (!email) {
      return new Response(JSON.stringify({
        success: false,
        message: 'Email is required'
      }), {
        status: 400,
        headers: { 'Content-Type': 'application/json' }
      });
    }

    const [users] = await connection.execute(
      'SELECT id, name, email, user_id, referrer_id, token_id, created_at, updated_at, plan_a FROM users WHERE email = ?',
      [email]
    );

    if (users.length === 0) {
      return new Response(JSON.stringify({
        success: false,
        message: 'User not found'
      }), {
        status: 404,
        headers: { 'Content-Type': 'application/json' }
      });
    }

    return new Response(JSON.stringify({
      success: true,
      data: users[0],
      message: 'Login successful'
    }), {
      status: 200,
      headers: { 'Content-Type': 'application/json' }
    });
  } catch (error) {
    console.error('Login error:', error);
    return new Response(JSON.stringify({
      success: false,
      message: 'Login failed',
      error: error.message
    }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    });
  } finally {
    if (connection) await connection.end();
  }
}

export async function OPTIONS() {
  return new Response(null, {
    status: 200,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    }
  });
}