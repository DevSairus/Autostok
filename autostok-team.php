<?php
// Cargar configuración
$configData = file_exists(__DIR__ . '/data/configuracion.json') 
    ? json_decode(file_get_contents(__DIR__ . '/data/configuracion.json'), true) 
    : [];

$config = $configData['general'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Autostok Team - Automovilismo Competitivo</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #000;
      color: #fff;
      overflow-x: hidden;
      position: relative;
    }

    /* FONDO ANIMADO DE PISTA */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        repeating-linear-gradient(
          90deg,
          transparent,
          transparent 45px,
          rgba(255,215,0,0.03) 45px,
          rgba(255,215,0,0.03) 50px
        ),
        repeating-linear-gradient(
          0deg,
          transparent,
          transparent 95px,
          rgba(255,215,0,0.02) 95px,
          rgba(255,215,0,0.02) 100px
        );
      animation: trackMovement 20s linear infinite;
      z-index: -1;
      opacity: 0.3;
    }

    @keyframes trackMovement {
      0% { transform: translateY(0); }
      100% { transform: translateY(100px); }
    }

    /* ANIMACIONES PERSONALIZADAS DE RACING */
    @keyframes pitStopIndicator {
      0%, 100% { 
        transform: translateX(0) scaleX(1);
        opacity: 0.8;
      }
      50% { 
        transform: translateX(10px) scaleX(1.1);
        opacity: 1;
      }
    }

    @keyframes speedStreak {
      0% { 
        transform: translateX(-200%) skewX(-20deg);
        opacity: 0;
      }
      50% {
        opacity: 0.6;
      }
      100% { 
        transform: translateX(200%) skewX(-20deg);
        opacity: 0;
      }
    }

    @keyframes podiumRise {
      0% { 
        transform: translateY(100%) scale(0.8);
        opacity: 0;
      }
      60% {
        transform: translateY(-10%) scale(1.05);
      }
      100% { 
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }

    @keyframes championGlow {
      0%, 100% { 
        box-shadow: 
          0 0 20px rgba(255,215,0,0.4),
          0 0 40px rgba(255,215,0,0.2),
          inset 0 0 20px rgba(255,215,0,0.1);
      }
      50% { 
        box-shadow: 
          0 0 40px rgba(255,215,0,0.8),
          0 0 80px rgba(255,215,0,0.4),
          inset 0 0 30px rgba(255,215,0,0.2);
      }
    }

    @keyframes flagWave {
      0% { 
        transform: translateY(0) rotate(0deg);
      }
      50% { 
        transform: translateY(-5px) rotate(2deg);
      }
      100% { 
        transform: translateY(0) rotate(0deg);
      }
    }

    @keyframes raceStart {
      0% { 
        opacity: 0;
      }
      100% { 
        opacity: 1;
      }
    }

    @keyframes lapCounter {
      0% { 
        transform: rotateY(90deg) scale(0.5);
        opacity: 0;
      }
      50% {
        transform: rotateY(0deg) scale(1.1);
      }
      100% { 
        transform: rotateY(0deg) scale(1);
        opacity: 1;
      }
    }

    @keyframes teamRadio {
      0%, 100% {
        transform: scale(1);
        filter: brightness(1);
      }
      50% {
        transform: scale(1.05);
        filter: brightness(1.2);
      }
    }

    @keyframes dashboardFlicker {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }

    @keyframes turboBurst {
      0% {
        transform: scale(1) translateX(0);
        opacity: 1;
        filter: blur(0);
      }
      100% {
        transform: scale(2) translateX(-100px);
        opacity: 0;
        filter: blur(15px);
      }
    }

    /* Header con efecto de fibra de carbono */
    .header {
      position: fixed;
      top: 0;
      width: 100%;
      background: 
        linear-gradient(135deg, rgba(30,30,30,0.98) 0%, rgba(0,0,0,0.98) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.03) 2px, rgba(255,215,0,0.03) 4px);
      backdrop-filter: blur(10px);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      border-bottom: 3px solid #FFD700;
      box-shadow: 
        0 4px 20px rgba(255,215,0,0.3),
        0 -1px 0 rgba(255,215,0,0.1) inset;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: #FFD700;
      text-shadow: 
        0 0 10px rgba(255,215,0,0.8),
        0 0 20px rgba(255,215,0,0.6),
        2px 2px 4px rgba(0,0,0,0.8);
      cursor: pointer;
      white-space: nowrap;
      animation: championGlow 2s ease-in-out infinite;
      position: relative;
    }

    .logo::before {
      content: '';
      position: absolute;
      top: 50%;
      left: -30px;
      width: 20px;
      height: 2px;
      background: linear-gradient(90deg, transparent, #FFD700);
      animation: speedStreak 2s ease-in-out infinite;
    }

    .header nav {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .header nav a {
      color: #fff;
      text-decoration: none;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      position: relative;
      white-space: nowrap;
      padding: 5px 10px;
    }

    .header nav a::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,215,0,0.2), transparent);
      transform: translateX(-100%);
      transition: transform 0.5s ease;
    }

    .header nav a:hover::before {
      transform: translateX(100%);
    }

    .header nav a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: #FFD700;
      transition: width 0.3s ease;
      box-shadow: 0 0 10px rgba(255,215,0,0.8);
    }

    .header nav a:hover::after {
      width: 100%;
    }

    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
    }

    .menu-toggle span {
      width: 25px;
      height: 3px;
      background: #FFD700;
      border-radius: 2px;
      transition: all 0.3s ease;
      box-shadow: 0 0 5px rgba(255,215,0,0.5);
    }

    .menu-toggle.active span:nth-child(1) {
      transform: rotate(45deg) translate(8px, 8px);
    }

    .menu-toggle.active span:nth-child(2) {
      opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
      transform: rotate(-45deg) translate(7px, -7px);
    }

    /* Navegación de secciones estilo pit board */
    .section-nav {
      position: fixed;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 999;
      display: flex;
      gap: 8px;
      background: 
        linear-gradient(135deg, rgba(0,0,0,0.95) 0%, rgba(30,30,30,0.95) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.05) 2px, rgba(255,215,0,0.05) 4px);
      padding: 12px 15px;
      border-radius: 30px;
      border: 2px solid rgba(255,215,0,0.5);
      backdrop-filter: blur(10px);
      flex-wrap: wrap;
      justify-content: center;
      max-width: 90%;
      max-height: 100px;
      overflow-y: auto;
      box-shadow: 
        0 0 30px rgba(255,215,0,0.4),
        0 5px 15px rgba(0,0,0,0.5);
    }

    .section-btn {
      padding: 8px 16px;
      background: rgba(255,215,0,0.1);
      color: #fff;
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.85rem;
      white-space: nowrap;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
    }

    .section-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,215,0,0.4), transparent);
      transition: left 0.5s ease;
    }

    .section-btn:hover {
      background: rgba(255,215,0,0.2);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255,215,0,0.4);
    }

    .section-btn:hover::before {
      left: 100%;
    }

    .section-btn.active {
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      font-weight: bold;
      border-color: #FFD700;
      animation: championGlow 2s infinite, teamRadio 1s ease-in-out infinite;
    }

    /* Contenedor principal */
    .container {
      width: 100%;
      min-height: 100vh;
      padding-top: 70px;
    }

    /* Secciones */
    .section {
      display: none;
      width: 100%;
      min-height: calc(100vh - 70px);
      padding: 90px 20px 50px;
    }

    .section.active {
      display: block;
      animation: raceStart 0.5s ease-out;
    }

    /* Sección Bienvenida con efectos de circuito */
    .welcome-section {
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      background: linear-gradient(135deg, rgba(255,215,0,0.05), transparent);
      padding: 40px 20px;
      position: relative;
      overflow: hidden;
    }

    .welcome-section.active {
      display: flex !important;
    }

    .welcome-section::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background: 
        repeating-conic-gradient(
          from 0deg at 50% 50%,
          rgba(255,215,0,0.03) 0deg,
          transparent 45deg,
          rgba(255,215,0,0.03) 90deg
        );
      animation: trackMovement 30s linear infinite;
      opacity: 0.3;
    }

    /* Imagen de hero con auto */
    .hero-image-container {
      position: relative;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto 40px;
      z-index: 2;
    }

    .hero-image {
      width: 100%;
      height: auto;
      border-radius: 15px;
      border: 3px solid rgba(255,215,0,0.3);
      box-shadow: 0 10px 40px rgba(255,215,0,0.3);
    }

    .welcome-section h1 {
      font-size: clamp(2rem, 6vw, 4rem);
      color: #FFD700;
      margin-bottom: 20px;
      text-transform: uppercase;
      letter-spacing: 3px;
      animation: championGlow 3s ease-in-out infinite;
      position: relative;
      z-index: 2;
      text-shadow: 
        0 0 20px rgba(255,215,0,0.8),
        0 0 40px rgba(255,215,0,0.4),
        3px 3px 6px rgba(0,0,0,0.8);
    }

    .welcome-section h1::before {
      content: '';
      position: absolute;
      top: 50%;
      left: -50px;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, transparent, #FFD700);
      animation: speedStreak 2s ease-in-out infinite;
    }

    .welcome-section h1::after {
      content: '';
      position: absolute;
      top: 50%;
      right: -50px;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #FFD700, transparent);
      animation: speedStreak 2s ease-in-out infinite reverse;
    }

    .welcome-section p {
      font-size: clamp(1rem, 3vw, 1.5rem);
      color: rgba(255,255,255,0.9);
      max-width: 900px;
      margin-bottom: 30px;
      line-height: 1.8;
      position: relative;
      z-index: 2;
      animation: podiumRise 1.5s ease-out 0.3s backwards;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      max-width: 1000px;
      margin-top: 40px;
      width: 100%;
      position: relative;
      z-index: 2;
    }

    .stat-card {
      background: 
        linear-gradient(135deg, rgba(255,215,0,0.1) 0%, rgba(0,0,0,0.3) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.03) 2px, rgba(255,215,0,0.03) 4px);
      padding: 25px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      animation: podiumRise 0.8s ease-out backwards;
    }

    .stat-card:nth-child(1) { animation-delay: 0.2s; }
    .stat-card:nth-child(2) { animation-delay: 0.4s; }
    .stat-card:nth-child(3) { animation-delay: 0.6s; }
    .stat-card:nth-child(4) { animation-delay: 0.8s; }

    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,215,0,0.15) 0%, transparent 70%);
      animation: dashboardFlicker 2s ease-in-out infinite;
    }

    .stat-card:hover {
      transform: translateY(-10px) scale(1.05);
      border-color: #FFD700;
      box-shadow: 
        0 0 40px rgba(255,215,0,0.5), 
        inset 0 0 30px rgba(255,215,0,0.15);
    }

    .stat-number {
      font-size: clamp(2rem, 4vw, 3rem);
      color: #FFD700;
      font-weight: bold;
      margin-bottom: 10px;
      animation: lapCounter 1s ease-out backwards;
      position: relative;
      z-index: 1;
      text-shadow: 
        0 0 10px rgba(255,215,0,0.8),
        2px 2px 4px rgba(0,0,0,0.8);
    }

    .stat-label {
      font-size: 0.95rem;
      color: rgba(255,255,255,0.8);
      position: relative;
      z-index: 1;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    /* Sección de Objetivos */
    .objetivos-section {
      max-width: 1000px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .objetivos-section h2 {
      font-size: clamp(1.8rem, 5vw, 3rem);
      color: #FFD700;
      margin-bottom: 40px;
      text-transform: uppercase;
      text-align: center;
      animation: championGlow 3s ease-in-out infinite;
      text-shadow: 
        0 0 20px rgba(255,215,0,0.8),
        0 0 40px rgba(255,215,0,0.4),
        3px 3px 6px rgba(0,0,0,0.8);
    }

    .objetivos-list {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .objetivo-item {
      background: 
        linear-gradient(135deg, rgba(255,215,0,0.08) 0%, rgba(0,0,0,0.5) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.02) 2px, rgba(255,215,0,0.02) 4px);
      padding: 25px 25px 25px 70px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      position: relative;
      transition: all 0.3s ease;
      font-size: 1.1rem;
      line-height: 1.8;
      color: rgba(255,255,255,0.9);
      text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    .objetivo-item::before {
      content: '🏁';
      position: absolute;
      left: 25px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 2rem;
      animation: flagWave 2s ease-in-out infinite;
    }

    .objetivo-item:hover {
      transform: translateX(10px);
      border-color: #FFD700;
      box-shadow: 0 5px 20px rgba(255,215,0,0.4);
    }

    /* Sección de Logros estilo pit garage */
    .logros-section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
      position: relative;
    }

    .logros-section h2 {
      font-size: clamp(1.8rem, 5vw, 3rem);
      color: #FFD700;
      margin-bottom: 40px;
      text-transform: uppercase;
      text-align: center;
      animation: championGlow 3s ease-in-out infinite;
      text-shadow: 
        0 0 20px rgba(255,215,0,0.8),
        0 0 40px rgba(255,215,0,0.4),
        3px 3px 6px rgba(0,0,0,0.8);
    }

    /* Galería de imágenes de campeonatos */
    .championship-gallery {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
    }

    .championship-image {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
      aspect-ratio: 3/4;
    }

    .championship-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .championship-image:hover {
      transform: translateY(-10px);
      border-color: #FFD700;
      box-shadow: 0 15px 40px rgba(255,215,0,0.5);
    }

    .championship-image:hover img {
      transform: scale(1.1);
    }

    .logros-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .logro-card {
      background: 
        linear-gradient(135deg, rgba(255,215,0,0.08) 0%, rgba(0,0,0,0.5) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.02) 2px, rgba(255,215,0,0.02) 4px);
      padding: 25px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      animation: podiumRise 0.8s ease-out backwards;
    }

    .logro-card:nth-child(1) { animation-delay: 0.1s; }
    .logro-card:nth-child(2) { animation-delay: 0.3s; }
    .logro-card:nth-child(3) { animation-delay: 0.5s; }

    .logro-card::before {
      content: '🏆';
      position: absolute;
      top: -20px;
      right: -20px;
      font-size: 120px;
      opacity: 0.08;
      animation: flagWave 4s ease-in-out infinite;
      filter: drop-shadow(0 0 10px rgba(255,215,0,0.3));
    }

    .logro-card:hover {
      transform: translateY(-8px);
      border-color: #FFD700;
      box-shadow: 
        0 10px 40px rgba(255,215,0,0.4), 
        inset 0 0 20px rgba(255,215,0,0.1);
    }

    .logro-card h3 {
      color: #FFD700;
      font-size: 1.3rem;
      margin-bottom: 15px;
      border-bottom: 2px solid rgba(255,215,0,0.3);
      padding-bottom: 10px;
      text-shadow: 
        0 0 10px rgba(255,215,0,0.6),
        2px 2px 4px rgba(0,0,0,0.8);
      position: relative;
    }

    .logro-card ul {
      list-style: none;
      padding: 0;
    }

    .logro-card li {
      color: rgba(255,255,255,0.85);
      padding: 8px 0;
      padding-left: 25px;
      position: relative;
      font-size: 0.95rem;
      line-height: 1.6;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    .logro-card li::before {
      content: '▶';
      position: absolute;
      left: 0;
      color: #FFD700;
      font-size: 0.8rem;
    }

    .logro-card li:hover {
      color: #FFD700;
      padding-left: 30px;
      text-shadow: 0 0 10px rgba(255,215,0,0.6);
    }

    /* Sección de Eventos */
    .eventos-section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .eventos-section h2 {
      font-size: clamp(1.8rem, 5vw, 3rem);
      color: #FFD700;
      margin-bottom: 40px;
      text-transform: uppercase;
      text-align: center;
      animation: championGlow 3s ease-in-out infinite;
      text-shadow: 
        0 0 20px rgba(255,215,0,0.8),
        0 0 40px rgba(255,215,0,0.4),
        3px 3px 6px rgba(0,0,0,0.8);
    }

    .eventos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }

    .evento-card {
      background: 
        linear-gradient(135deg, rgba(255,215,0,0.08) 0%, rgba(0,0,0,0.5) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.02) 2px, rgba(255,215,0,0.02) 4px);
      padding: 25px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
    }

    .evento-card:hover {
      transform: translateY(-8px);
      border-color: #FFD700;
      box-shadow: 0 10px 30px rgba(255,215,0,0.4);
    }

    .evento-image {
      width: 100%;
      height: 200px;
      border-radius: 10px;
      margin-bottom: 15px;
      overflow: hidden;
      border: 2px solid rgba(255,215,0,0.2);
    }

    .evento-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .evento-card:hover .evento-image img {
      transform: scale(1.1);
    }

    .evento-title {
      color: #FFD700;
      font-size: 1.3rem;
      margin-bottom: 10px;
      font-weight: bold;
      text-shadow: 0 0 10px rgba(255,215,0,0.6);
    }

    .evento-details {
      color: rgba(255,255,255,0.85);
      line-height: 1.8;
      margin-bottom: 10px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    .evento-alcance {
      color: #FFD700;
      font-weight: bold;
      font-size: 1.1rem;
      text-shadow: 0 0 10px rgba(255,215,0,0.6);
    }

    /* Sección de Pilotos estilo team roster */
    .pilotos-section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .pilotos-section h2 {
      font-size: clamp(1.8rem, 5vw, 3rem);
      color: #FFD700;
      margin-bottom: 40px;
      text-transform: uppercase;
      text-align: center;
      animation: championGlow 3s ease-in-out infinite;
      text-shadow: 
        0 0 20px rgba(255,215,0,0.8),
        0 0 40px rgba(255,215,0,0.4),
        3px 3px 6px rgba(0,0,0,0.8);
    }

    .pilotos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .piloto-card {
      background: 
        linear-gradient(135deg, rgba(255,215,0,0.12) 0%, rgba(0,0,0,0.6) 100%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,215,0,0.03) 2px, rgba(255,215,0,0.03) 4px);
      padding: 30px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
      text-align: center;
      position: relative;
      overflow: hidden;
      animation: podiumRise 0.8s ease-out backwards;
    }

    .piloto-card:nth-child(1) { animation-delay: 0.2s; }
    .piloto-card:nth-child(2) { animation-delay: 0.4s; }
    .piloto-card:nth-child(3) { animation-delay: 0.6s; }
    .piloto-card:nth-child(4) { animation-delay: 0.8s; }

    .piloto-card:hover {
      transform: scale(1.05);
      border-color: #FFD700;
      box-shadow: 
        0 15px 50px rgba(255,215,0,0.5), 
        inset 0 0 30px rgba(255,215,0,0.15);
    }

    /* Espacio para foto del piloto */
    .piloto-foto {
      width: 150px;
      height: 150px;
      margin: 0 auto 20px;
      border-radius: 50%;
      overflow: hidden;
      border: 3px solid #FFD700;
      box-shadow: 0 0 20px rgba(255,215,0,0.5);
      position: relative;
      z-index: 1;
    }

    .piloto-foto img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .piloto-foto.placeholder {
      background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(0,0,0,0.5));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
    }

    .piloto-nombre {
      color: #FFD700;
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 20px;
      text-transform: uppercase;
      letter-spacing: 2px;
      animation: lapCounter 1s ease-out, championGlow 2s ease-in-out infinite;
      text-shadow: 
        0 0 15px rgba(255,215,0,0.8),
        2px 2px 4px rgba(0,0,0,0.8);
      position: relative;
    }

    .piloto-nombre::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 2px;
      background: linear-gradient(90deg, transparent, #FFD700, transparent);
      box-shadow: 0 0 10px rgba(255,215,0,0.8);
    }

    .piloto-logros {
      list-style: none;
      padding: 0;
      text-align: left;
    }

    .piloto-logros li {
      color: rgba(255,255,255,0.85);
      padding: 8px 0;
      padding-left: 25px;
      position: relative;
      font-size: 0.9rem;
      line-height: 1.6;
      border-bottom: 1px solid rgba(255,215,0,0.1);
      transition: all 0.3s ease;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    .piloto-logros li:hover {
      padding-left: 35px;
      color: #FFD700;
      border-bottom-color: rgba(255,215,0,0.5);
      background: rgba(255,215,0,0.05);
    }

    .piloto-logros li:last-child {
      border-bottom: none;
    }

    .piloto-logros li::before {
      content: '🏁';
      position: absolute;
      left: 0;
      animation: flagWave 2s ease-in-out infinite;
      font-size: 1rem;
      filter: drop-shadow(0 0 5px rgba(255,215,0,0.5));
    }

    .piloto-quote {
      margin-top: 20px;
      padding-top: 15px;
      border-top: 2px solid rgba(255,215,0,0.3);
      font-style: italic;
      color: rgba(255,215,0,0.9);
      font-size: 0.9rem;
      animation: podiumRise 0.8s ease-out;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
      position: relative;
    }

    .piloto-quote::before {
      content: '"';
      position: absolute;
      left: -10px;
      top: 10px;
      font-size: 2rem;
      color: rgba(255,215,0,0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header {
        padding: 12px 15px;
      }

      .logo {
        font-size: 1.2rem;
      }

      .header nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        flex-direction: column;
        background: rgba(0,0,0,0.98);
        padding: 20px;
        gap: 10px;
        border-bottom: 2px solid #FFD700;
        animation: podiumRise 0.5s ease-out;
      }

      .header nav.active {
        display: flex;
      }

      .menu-toggle {
        display: flex;
      }

      .section-nav {
        top: 65px;
        max-width: 95%;
        padding: 10px 12px;
      }

      .section-btn {
        font-size: 0.75rem;
        padding: 6px 12px;
      }

      .container {
        padding-top: 150px;
      }

      .section {
        padding: 150px 15px 40px;
      }

      .logros-grid, .pilotos-grid, .eventos-grid, .championship-gallery {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      .logo {
        font-size: 1rem;
      }

      .section-btn {
        font-size: 0.7rem;
        padding: 5px 10px;
      }

      .container {
        padding-top: 130px;
      }

      .section {
        padding: 130px 12px 30px;
      }

      .welcome-section h1 {
        font-size: 1.8rem;
      }

      .welcome-section p {
        font-size: 0.95rem;
      }
    }

    /* Scrollbar personalizado */
    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: rgba(0,0,0,0.5);
      border-left: 1px solid rgba(255,215,0,0.2);
    }

    ::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #FFD700, #FFA500);
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(255,215,0,0.5);
    }

    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, #FFA500, #FFD700);
      box-shadow: 0 0 15px rgba(255,215,0,0.8);
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo">🏎️ AUTOSTOK TEAM</div>
    
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <nav id="navMenu">
      <a href="index.php">Inicio</a>
      <a href="vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios/servicios.php">Servicios</a>
      <a href="nosotros.php">Empresa</a>
      <a href="autostok-team.php" class="active">Team</a>
      <a href="contacto.php">Contacto</a>
    </nav>
  </header>

  <!-- Navegación de secciones -->
  <div class="section-nav">
    <button class="section-btn active" onclick="cambiarSeccion(0)">🏎️ Quiénes Somos</button>
    <button class="section-btn" onclick="cambiarSeccion(1)">🎯 Objetivos</button>
    <button class="section-btn" onclick="cambiarSeccion(2)">🏆 Logros 2024</button>
    <button class="section-btn" onclick="cambiarSeccion(3)">⏱️ Trayectoria</button>
    <button class="section-btn" onclick="cambiarSeccion(4)">📅 Eventos</button>
    <button class="section-btn" onclick="cambiarSeccion(5)">👤 Pilotos</button>
    <button class="section-btn" onclick="cambiarSeccion(6)">🤝 Redes Sociales</button>
  </div>

  <div class="container">
    
    <!-- Sección 0: Quiénes Somos -->
    <section class="section welcome-section active" data-section="0">
      <!-- ESPACIO PARA IMAGEN HERO DEL AUTO -->
      <!-- Guardar imagen como: images/team/hero-auto.png o .jpg -->
      <div class="hero-image-container">
        <img src="images/team/hero-auto.png" alt="Autostok Team Auto" class="hero-image">
      </div>

      <h1>Autostok Team</h1>
      <p>Somos el equipo más grande e importante a nivel nacional y Ecuatoriano. Contamos con una trayectoria de más de 27 años participando en las competencias automovilísticas de Colombia y en nuestro país vecino, Ecuador. Además de esto, también nos caracterizamos por tener la mayor cantidad de triunfos en todos los torneos nacionales; como también, el equipo con mayor consistencia dentro de nuestro deporte automotor.</p>
      
      <p>AutoStok Team es un equipo predominante en el automovilismo, que siempre está y estará presente dentro de los campeonatos más importantes de Colombia y Ecuador. Nuestra organización nos permite siempre estar luchando por cada victoria que se pueda obtener, contando con los mejores pilotos, preparadores y personal humano, los cuales son insignias en cada una de sus funciones dentro del equipo y dentro del Autódromo de Tocancipá.</p>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">27+</div>
          <div class="stat-label">Años de Trayectoria</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">6</div>
          <div class="stat-label">Campeones TC2000</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">19</div>
          <div class="stat-label">Títulos 6H Bogotá</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">15</div>
          <div class="stat-label">Campeonatos CNA</div>
        </div>
      </div>
    </section>

    <!-- Sección 1: Objetivos -->
    <section class="section" data-section="1">
      <div class="objetivos-section">
        <h2>🎯 Objetivos del Equipo</h2>
        
        <div class="objetivos-list">
          <div class="objetivo-item">
            Ser el mejor equipo a nivel nacional y en Ecuador, generando que nuestro deporte automotor crezca cada año mas.
          </div>
          
          <div class="objetivo-item">
            Ser los referentes en los campeonatos de TC2000 Colombia, como en CNA, siendo los mejores en las áreas del equipo que son: Organización, Personal Humano y técnico, pilotos representantes.
          </div>
          
          <div class="objetivo-item">
            Participar y ser referentes en los campeonatos de Colombia en le transcurso del año, como también estar en las 1000Kms de Ecuador y 6 horas de Bogotá.
          </div>
          
          <div class="objetivo-item">
            Seguir buscando y ayudando pilotos nuevos para que crezcan en su carrera Automovilística en nuestro país.
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 2: Logros 2024 -->
    <section class="section" data-section="2">
      <div class="logros-section">
        <h2>🏆 Logros 2024</h2>
        
        <!-- ESPACIO PARA IMÁGENES DE CAMPEONATOS -->
        <!-- Guardar imágenes como: images/team/campeon-tc2000.jpg, campeon-chase.jpg, campeon-marcas.jpg -->
        <div class="championship-gallery">
          <div class="championship-image">
            <img src="images/team/campeon-tc2000.jpg" alt="Campeón TC2000 2024">
          </div>
          <div class="championship-image">
            <img src="images/team/campeon-chase.jpg" alt="Campeón CHASE 2024">
          </div>
          <div class="championship-image">
            <img src="images/team/campeon-marcas.jpg" alt="Campeón de Marcas 2024">
          </div>
        </div>

        <div class="logros-grid">
          <div class="logro-card">
            <h3>🏁 TC2000 Colombia</h3>
            <ul>
              <li>Campeón TC2000 2024</li>
              <li>Campeón CHASE 2024</li>
              <li>Record de Victorias (10 de 15 carreras)</li>
              <li>3 Pole Positions de 5 Quallys</li>
              <li>14 Top 5 obtenidos</li>
              <li>Campeón de Marcas</li>
              <li>Campeón de Preparadores</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>🥇 Campeonato Nacional</h3>
            <ul>
              <li>3er puesto Categoría ST2</li>
              <li>3 Top 5 en 6 carreras</li>
              <li>Campeón 6 Horas de Bogotá</li>
              <li>5 fechas disputadas</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>📊 Estadísticas 2024</h3>
            <ul>
              <li>6 fechas TC2000</li>
              <li>15 carreras totales</li>
              <li>Alcance: 1,300 personas por evento</li>
              <li>Participación en Track Days</li>
              <li>Capacitaciones de Seguridad Vial</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 3: Trayectoria -->
    <section class="section" data-section="3">
      <div class="logros-section">
        <h2>⏱️ Nuestra Trayectoria</h2>
        
        <div class="logros-grid">
          <div class="logro-card">
            <h3>🏁 TC2000 Colombia</h3>
            <ul>
              <li>6 Campeonatos Nacionales</li>
              <li>7 Campeonatos CHASE TC2000</li>
              <li>2 Campeonatos TC Junior</li>
              <li>4 Campeonatos de Marca</li>
              <li>6 Campeonatos de Preparadores</li>
              <li>Ganadores carreras 600, 700, 800, 900 y 1000</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>🏆 Campeonatos Nacionales</h3>
            <ul>
              <li>15 Campeonatos CNA</li>
              <li>19 Títulos 6 Horas Bogotá</li>
              <li>3 Campeonatos 6H Ecuador (Internacional)</li>
              <li>1 Copa CATI Ecuador (Internacional)</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>🌟 Presencia Destacada</h3>
            <ul>
              <li>Equipo más grande a nivel nacional</li>
              <li>Mayor cantidad de triunfos históricos</li>
              <li>Mejor consistencia en automovilismo</li>
              <li>Presencia en Colombia y Ecuador</li>
              <li>Referente en categorías TC y CNA</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 4: Eventos -->
    <section class="section" data-section="4">
      <div class="eventos-section">
        <h2>📅 Eventos y Alcance</h2>
        
        <div class="eventos-grid">
          <div class="evento-card">
            <!-- ESPACIO PARA IMAGEN DE TC2000 -->
            <!-- Guardar como: images/team/evento-tc2000.jpg -->
            <div class="evento-image">
              <img src="images/team/evento-tc2000.jpg" alt="TC2000 Colombia">
            </div>
            <div class="evento-title">PARTICIPACIÓN TC2000 COLOMBIA</div>
            <div class="evento-details">
              7 Fechas – 15 Carreras.
            </div>
            <div class="evento-alcance">Alcance TOTAL por Evento: 1300 Personas</div>
          </div>

          <div class="evento-card">
            <!-- ESPACIO PARA IMAGEN DE CNA -->
            <!-- Guardar como: images/team/evento-cna.jpg -->
            <div class="evento-image">
              <img src="images/team/evento-cna.jpg" alt="Campeonato CNA">
            </div>
            <div class="evento-title">PARTICIPACIÓN CAMPEONATO CNA</div>
            <div class="evento-details">
              5 Fechas – 8 Carreras.
            </div>
            <div class="evento-alcance">Alcance TOTAL por Evento: 750 Personas</div>
          </div>

          <div class="evento-card">
            <!-- ESPACIO PARA IMAGEN DE TRACK DAY -->
            <!-- Guardar como: images/team/evento-trackday.jpg -->
            <div class="evento-image">
              <img src="images/team/evento-trackday.jpg" alt="Track Day Colombia">
            </div>
            <div class="evento-title">Acompañamiento TRACK DAY COLOMBIA</div>
            <div class="evento-details">
              2 Fechas.
            </div>
            <div class="evento-alcance">Alcance TOTAL por Evento: 350 Personas</div>
          </div>

          <div class="evento-card">
            <!-- ESPACIO PARA IMAGEN DE CAPACITACIÓN -->
            <!-- Guardar como: images/team/evento-capacitacion.jpg -->
            <div class="evento-image">
              <img src="images/team/evento-capacitacion.jpg" alt="Capacitación Seguridad Vial">
            </div>
            <div class="evento-title">CAPACITACIÓN SEGURIDAD VIAL SABANA</div>
            <div class="evento-details">
              4 Capacitaciones.
            </div>
            <div class="evento-alcance">Alcance TOTAL por Evento: 400 Personas</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 5: Pilotos -->
    <section class="section" data-section="5">
      <div class="pilotos-section">
        <h2>👤 Nuestros Pilotos</h2>
        
        <div class="pilotos-grid">
          <div class="piloto-card">
            <!-- ESPACIO PARA FOTO DE CAMILO FORERO -->
            <!-- Guardar como: images/team/piloto-camilo-forero.jpg -->
            <div class="piloto-foto">
              <img src="images/team/piloto-camilo-forero.jpg" alt="Camilo Forero">
            </div>
            <div class="piloto-nombre">Camilo Forero</div>
            <ul class="piloto-logros">
              <li>TETRA Campeón TC2000 (2017, 2021, 2023, 2024)</li>
              <li>Campeón más Joven de TC2000 (18 años)</li>
              <li>7 veces Campeón 6 Horas Bogotá</li>
              <li>7 veces Campeón CNA</li>
              <li>3 veces Subcampeón TC2000</li>
              <li>Piloto con más vueltas lideradas</li>
              <li>Ganador carreras 700 y 800 TC2000</li>
              <li>Campeón 6 Horas Ecuador</li>
            </ul>
            <div class="piloto-quote">"Para ganar, lo primero que hay que hacer es llegar"</div>
          </div>

          <div class="piloto-card">
            <!-- ESPACIO PARA FOTO DE JUAN FELIPE GARCÍA -->
            <!-- Guardar como: images/team/piloto-juan-garcia.jpg -->
            <div class="piloto-foto">
              <img src="images/team/piloto-juan-garcia.jpg" alt="Juan Felipe García">
            </div>
            <div class="piloto-nombre">Juan Felipe García</div>
            <ul class="piloto-logros">
              <li>Campeón Go Karts Junior</li>
              <li>6º en Rally de las Américas</li>
              <li>Campeón Monomarca Nissan March</li>
              <li>5 veces Campeón 6H Bogotá</li>
              <li>Subcampeón TC2000 2020</li>
              <li>15 Top Five en TC2000</li>
              <li>4º puesto TC2000 Colombia</li>
            </ul>
            <div class="piloto-quote">"La velocidad es solo una cifra en el velocímetro, la verdadera carrera es en la mente"</div>
          </div>

          <div class="piloto-card">
            <!-- ESPACIO PARA FOTO DE ANDRÉS RODRÍGUEZ -->
            <!-- Guardar como: images/team/piloto-andres-rodriguez.jpg -->
            <div class="piloto-foto">
              <img src="images/team/piloto-andres-rodriguez.jpg" alt="Andrés Rodríguez">
            </div>
            <div class="piloto-nombre">Andrés Rodríguez</div>
            <ul class="piloto-logros">
              <li>3 veces Campeón Shifter USA</li>
              <li>3 veces Campeón 6H Bogotá</li>
              <li>Campeón TC Junior</li>
              <li>2 veces Subcampeón TC2000</li>
              <li>Participante permanente en campeonatos nacionales</li>
            </ul>
            <div class="piloto-quote">"El éxito en las pistas comienza con disciplina y dedicación"</div>
          </div>

          <div class="piloto-card">
            <!-- ESPACIO PARA FOTO DE JIMMY RAMÍREZ -->
            <!-- Guardar como: images/team/piloto-jimmy-ramirez.jpg -->
            <div class="piloto-foto">
              <img src="images/team/piloto-jimmy-ramirez.jpg" alt="Jimmy Ramírez">
            </div>
            <div class="piloto-nombre">Jimmy Ramírez</div>
            <ul class="piloto-logros">
              <li>2 veces Campeón CHASE TC2000</li>
              <li>2 veces Campeón TC2000 Colombia</li>
              <li>2 veces Campeón 6H Bogotá</li>
              <li>Segundo puesto TC2000 Colombia</li>
              <li>Referente en categoría nacional</li>
            </ul>
            <div class="piloto-quote">"En la pista, los campeones no nacen, se hacen con trabajo y pasión"</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 6: Redes Sociales -->
    <section class="section" data-section="6">
      <div class="logros-section">
        <h2>🤝 Redes Sociales, Experiencias y Vivencias</h2>
        
        <!-- ESPACIO PARA IMAGEN DE REDES SOCIALES -->
        <!-- Guardar como: images/team/redes-sociales.jpg -->
        <div class="hero-image-container" style="margin-bottom: 40px;">
          <img src="images/team/redes-sociales.png" alt="Redes Sociales Autostok Team" class="hero-image">
        </div>

        <div style="text-align: center; background: linear-gradient(135deg, rgba(255,215,0,0.08) 0%, rgba(0,0,0,0.5) 100%); padding: 50px 30px; border-radius: 15px; border: 2px solid rgba(255,215,0,0.3); margin-bottom: 40px; animation: podiumRise 0.8s ease-out;">
          <p style="font-size: 1.3rem; color: rgba(255,255,255,0.9); line-height: 2; margin-bottom: 30px; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
            El equipo <strong style="color: #FFD700;">Autostok Team</strong> es un grupo de apasionados por los deportes de motor que se dedica a llevar la emoción de las carreras a otro nivel. Con una trayectoria destacada en el mundo del automovilismo, nuestro equipo se compromete a ofrecer experiencias inigualables tanto en la pista como fuera de ella. Cada miembro del equipo <strong style="color: #FFD700;">Autostok Team</strong> aporta su talento y dedicación para alcanzar el éxito y superar nuevos retos. ¡Síguenos y descubre el mundo de la velocidad y la adrenalina junto a nosotros!
          </p>
          <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); margin-top: 30px; font-style: italic; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
            ¡Gracias por ser parte de nuestra historia!
          </p>
        </div>

        <div class="logros-grid">
          <div class="logro-card">
            <h3>📱 Redes Sociales</h3>
            <ul>
              <li>Instagram: @autostotkteam</li>
              <li>Facebook: Autostok Team</li>
              <li>Cobertura digital permanente</li>
              <li>Experiencias y vivencias del equipo</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>📊 Alcance de Medios</h3>
            <ul>
              <li>Free Press: $3.154'000.000</li>
              <li>Exposición Directa: $350'000.000</li>
              <li>Impacto Digital: $730'000.000</li>
              <li>Televisión Cable: $1.334'000.000</li>
            </ul>
          </div>

          <div class="logro-card">
            <h3>🎯 Presencia en Medios</h3>
            <ul>
              <li>14 notas en Prensa Digital</li>
              <li>10 Boletines de Prensa</li>
              <li>6 notas en Prensa Tradicional</li>
              <li>18 coberturas por Canal Capital</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

  </div>

  <?php include 'footer.php'; ?>

<script>
  // Menú mobile
  const menuToggle = document.getElementById('menuToggle');
  const navMenu = document.getElementById('navMenu');

  if (menuToggle && navMenu) {
    menuToggle.addEventListener('click', () => {
      menuToggle.classList.toggle('active');
      navMenu.classList.toggle('active');
    });

    navMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menuToggle.classList.remove('active');
        navMenu.classList.remove('active');
      });
    });
  }

  // Navegación de secciones
  function cambiarSeccion(index) {
    // Obtener todas las secciones y botones
    const todasLasSecciones = document.querySelectorAll('.section');
    const todosLosBotones = document.querySelectorAll('.section-btn');
    
    // Remover clase active de todas las secciones
    todasLasSecciones.forEach(seccion => {
      seccion.classList.remove('active');
    });
    
    // Remover clase active de todos los botones
    todosLosBotones.forEach(boton => {
      boton.classList.remove('active');
    });
    
    // Activar la sección correspondiente
    if (todasLasSecciones[index]) {
      todasLasSecciones[index].classList.add('active');
    }
    
    // Activar el botón correspondiente
    if (todosLosBotones[index]) {
      todosLosBotones[index].classList.add('active');
    }
    
    // Scroll al inicio
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // Hacer la función global
  window.cambiarSeccion = cambiarSeccion;
</script>

</body>
</html>