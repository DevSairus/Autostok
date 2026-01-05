# 📧 Sistema de Correos Automáticos - Guía de Instalación

## 📋 Descripción General

Este sistema envía correos automáticos cuando:
- ✅ Se crea una nueva **cita** → Email al cliente, sucursal y call center
- ✅ Se cambia el estado de una **cita** → Email al cliente
- ✅ Se crea una nueva **solicitud de compra** → Email al cliente, almacén y call center
- ✅ Se cambia el estado de una **solicitud** → Email al cliente

---

## 🚀 Instalación

### Paso 1: Instalar PHPMailer

Tienes dos opciones:

#### Opción A: Con Composer (Recomendado)
```bash
composer require phpmailer/phpmailer
```

#### Opción B: Instalación Manual
1. Descarga PHPMailer desde: https://github.com/PHPMailer/PHPMailer/releases
2. Extrae los archivos en: `/admin/api/PHPMailer/`
3. Debe quedar así:
   ```
   /admin/api/PHPMailer/
   ├── Exception.php
   ├── PHPMailer.php
   └── SMTP.php
   ```

### Paso 2: Copiar los Archivos del Sistema

Copia estos archivos a `/admin/api/`:

```
/admin/api/
├── mailer.php              (Sistema de envío de correos)
├── email-templates.php     (Plantillas HTML)
├── citas_updated.php       (Reemplaza citas.php)
└── solicitudes_updated.php (Reemplaza solicitudes.php)
```

### Paso 3: Renombrar Archivos

```bash
# Respaldar archivos originales
mv /admin/api/citas.php /admin/api/citas_backup.php
mv /admin/api/solicitudes.php /admin/api/solicitudes_backup.php

# Renombrar nuevos archivos
mv /admin/api/citas_updated.php /admin/api/citas.php
mv /admin/api/solicitudes_updated.php /admin/api/solicitudes.php
```

---

## ⚙️ Configuración

### 1. Configurar Gmail para SMTP

Si usas Gmail, necesitas crear una **contraseña de aplicación**:

1. Ve a tu cuenta de Google: https://myaccount.google.com/
2. Seguridad → Verificación en dos pasos (actívala si no lo está)
3. Seguridad → Contraseñas de aplicaciones
4. Selecciona "Correo" y "Otro (nombre personalizado)"
5. Escribe "AutoColombia" y genera
6. Copia la contraseña de 16 caracteres

### 2. Actualizar configuracion.json

Edita el archivo `/data/configuracion.json` y agrega estos campos en la sección `general`:

```json
{
  "general": {
    "nombre_empresa": "AutoColombia",
    "telefono": "+57 300 123 4567",
    "whatsapp": "573001234567",
    "email": "contacto@autocolombia.com",
    "direccion": "Calle 123 #45-67, Medellín, Colombia",
    
    // CONFIGURACIÓN SMTP (NUEVO)
    "smtp_host": "smtp.gmail.com",
    "smtp_port": 587,
    "smtp_user": "tu-email@gmail.com",
    "smtp_password": "xxxx xxxx xxxx xxxx",
    "smtp_secure": "tls",
    "email_from": "noreply@autocolombia.com",
    
    // DESTINATARIOS (NUEVO)
    "email_sucursal": "sucursal@autocolombia.com",
    "email_callcenter": "callcenter@autocolombia.com",
    "email_almacen": "almacen@autocolombia.com",
    
    "color_primario": "#007bff",
    "logo": "/images/logo.png"
  }
}
```

### Configuraciones SMTP Alternativas

#### Para Outlook/Hotmail:
```json
"smtp_host": "smtp-mail.outlook.com",
"smtp_port": 587,
"smtp_user": "tu-email@outlook.com",
"smtp_password": "tu-contraseña",
"smtp_secure": "tls"
```

#### Para Yahoo:
```json
"smtp_host": "smtp.mail.yahoo.com",
"smtp_port": 587,
"smtp_user": "tu-email@yahoo.com",
"smtp_password": "tu-contraseña-de-app",
"smtp_secure": "tls"
```

#### Para un servidor propio:
```json
"smtp_host": "mail.tudominio.com",
"smtp_port": 465,
"smtp_user": "correo@tudominio.com",
"smtp_password": "tu-contraseña",
"smtp_secure": "ssl"
```

---

## 🧪 Pruebas

### Prueba 1: Crear una Cita

```javascript
// Desde el frontend o Postman
fetch('/admin/api/citas.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    nombre: 'Juan Pérez',
    correo: 'juan@example.com',
    telefono: '3001234567',
    servicio_nombre: 'Mantenimiento',
    fecha: '2024-12-15',
    hora: '10:00 AM',
    notas: 'Primera visita'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

**Resultado esperado:**
```json
{
  "success": true,
  "message": "Cita solicitada",
  "id": 1733512345,
  "emails_enviados": {
    "cliente": {"success": true, "destinatario": "juan@example.com"},
    "sucursal": {"success": true, "destinatario": "sucursal@autocolombia.com"},
    "callcenter": {"success": true, "destinatario": "callcenter@autocolombia.com"}
  },
  "whatsapp_link": "https://wa.me/573001234567?text=..."
}
```

### Prueba 2: Cambiar Estado de Cita

```javascript
fetch('/admin/api/citas.php', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id: 1733512345,
    estado: 'confirmada'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

### Prueba 3: Crear Solicitud de Compra

```javascript
fetch('/admin/api/solicitudes.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    tipo: 'vehiculo',
    nombre: 'María García',
    correo: 'maria@example.com',
    telefono: '3009876543',
    vehiculo_id: 123,
    vehiculo_nombre: 'Toyota Corolla 2024',
    mensaje: 'Me interesa este vehículo'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

---

## 📊 Verificación de Logs

Los envíos de email se registran automáticamente en `/data/logs.json`:

```json
{
  "tipo": "email",
  "accion": "envio_cita",
  "detalles": {
    "cita_id": 1733512345,
    "cliente": "Juan Pérez",
    "resultados": {
      "cliente": {"success": true},
      "sucursal": {"success": true},
      "callcenter": {"success": true}
    }
  },
  "usuario": "Sistema",
  "fecha": "2024-12-06 14:30:45"
}
```

---

## 🎨 Personalización de Templates

### Modificar Colores y Estilos

Edita `/admin/api/email-templates.php`:

```php
// Cambiar color primario del header
$colorPrimario = $config['general']['color_primario'] ?? '#007bff';

// Cambiar fuente
font-family: 'Avenir', sans-serif;

// Cambiar tamaño de título
font-size: 28px;  // Ajustar según preferencia
```

### Agregar Logo en Emails

1. Sube tu logo a `/images/logo-email.png`
2. Edita `getTemplateBase()` en `email-templates.php`:

```php
<tr>
    <td style="background-color: {$colorPrimario}; padding: 30px 20px; text-align: center;">
        <img src="https://tudominio.com/images/logo-email.png" alt="{$nombreEmpresa}" style="max-width: 200px; margin-bottom: 10px;">
        <h1 style="margin: 0; color: #ffffff; font-size: 28px;">{$nombreEmpresa}</h1>
    </td>
</tr>
```

---

## 🔧 Solución de Problemas

### Error: "SMTP connect() failed"

**Causas comunes:**
1. ✅ Contraseña incorrecta
2. ✅ Puerto bloqueado por firewall
3. ✅ Verificación en 2 pasos no configurada (Gmail)
4. ✅ Contraseña de aplicación no generada

**Solución:**
```php
// En mailer.php, activar modo debug:
$mail->SMTPDebug = 2; // Muestra detalles del error
$mail->Debugoutput = 'html';
```

### Error: "Could not authenticate"

**Gmail:** Asegúrate de usar una contraseña de aplicación, no tu contraseña normal.

**Outlook:** Permite el acceso de apps menos seguras:
- Settings → Security → Additional security options
- App passwords → Create a new app password

### Emails no llegan (pero no hay error)

1. **Revisa la carpeta de SPAM** del destinatario
2. **Verifica el email remitente:** Algunos servidores rechazan emails si el dominio no coincide
3. **Prueba con otro servicio SMTP** (Gmail, Mailgun, SendGrid)

### Error: "File not found: mailer.php"

```bash
# Verifica que los archivos estén en la ubicación correcta
ls -la /admin/api/mailer.php
ls -la /admin/api/email-templates.php

# Verifica permisos
chmod 644 /admin/api/mailer.php
chmod 644 /admin/api/email-templates.php
```

---

## 📈 Mejoras Futuras Recomendadas

### 1. Cola de Emails (Queue)
Para manejar grandes volúmenes, implementar una cola:
```php
// Guardar emails pendientes en /data/email_queue.json
// Procesar con un cron job cada 5 minutos
```

### 2. Plantillas Personalizables desde Admin
Permitir editar los templates desde el panel de administración.

### 3. Notificaciones SMS
Integrar con un servicio como Twilio para enviar SMS.

### 4. Estadísticas de Emails
Dashboard mostrando tasa de apertura, clicks, etc.

### 5. Adjuntos Automáticos
Enviar PDF con detalles de la cita o cotización.

---

## 📞 Soporte

Si encuentras problemas:

1. Revisa los logs en `/data/logs.json`
2. Activa el modo debug de PHPMailer
3. Verifica la configuración SMTP
4. Prueba con un servicio de email alternativo

---

## ✅ Checklist de Instalación

- [ ] PHPMailer instalado
- [ ] Archivos copiados a `/admin/api/`
- [ ] Contraseña de aplicación generada (Gmail)
- [ ] `configuracion.json` actualizado con datos SMTP
- [ ] Emails de destino configurados
- [ ] Prueba de envío exitosa
- [ ] Logs verificados
- [ ] Templates personalizados (opcional)

---

**¡Sistema listo para producción! 🚀**