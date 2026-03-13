<?php

namespace App\Http\Controllers\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'EAV Project Management API',
    version: '1.0.0',
    description: 'A RESTful API for managing projects, timesheets, and dynamic EAV attributes with role-based access control.',
    contact: new OA\Contact(email: 'admin@example.com'),
    license: new OA\License(name: 'MIT'),
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Passport Bearer token'
)]
#[OA\Tag(name: 'Auth',       description: 'Authentication endpoints')]
#[OA\Tag(name: 'Attributes', description: 'EAV attribute management — admin only for write operations')]
#[OA\Tag(name: 'Projects',   description: 'Project management — managers/admins write, all auth users read')]
#[OA\Tag(name: 'Timesheets', description: 'Timesheet tracking — users manage their own, admins see all')]
#[OA\Tag(name: 'Reports',    description: 'Aggregated reporting endpoints')]
class OpenApiSpec {}
