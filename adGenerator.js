const Jimp = require('jimp');
const fs = require('fs');
const path = require('path');

const adsDir = path.join(__dirname, 'generated_ads');
if (!fs.existsSync(adsDir)) {
  fs.mkdirSync(adsDir, { recursive: true });
}

// Generate a static ad image with product details and uploaded photos
async function generateStaticAd(product, agent, baseUrl, uploadedPhotos = []) {
  const adId = Date.now().toString() + Math.random().toString(36).substr(2, 6);
  const outputPath = path.join(adsDir, `${adId}.png`);
  
  const width = 1080;
  const height = 1080;
  
  // Create a blank image with white background
  let image = new Jimp(width, height, '#ffffff');
  
  // Load fonts
  let fontLarge, fontMedium, fontSmall, fontBold;
  try {
    fontLarge = await Jimp.loadFont(Jimp.FONT_SANS_64_BLACK);
    fontMedium = await Jimp.loadFont(Jimp.FONT_SANS_32_BLACK);
    fontSmall = await Jimp.loadFont(Jimp.FONT_SANS_16_BLACK);
    fontBold = await Jimp.loadFont(Jimp.FONT_SANS_64_WHITE);
  } catch (err) {
    fontLarge = Jimp.FONT_SANS_64_BLACK;
    fontMedium = Jimp.FONT_SANS_32_BLACK;
    fontSmall = Jimp.FONT_SANS_16_BLACK;
    fontBold = Jimp.FONT_SANS_64_WHITE;
  }
  
  // Draw header background
  for (let x = 0; x < width; x++) {
    for (let y = 0; y < 180; y++) {
      image.setPixelColor(Jimp.rgbaToInt(37, 99, 235, 255), x, y);
    }
  }
  
  // Add product image if uploaded
  let yOffset = 180;
  if (uploadedPhotos && uploadedPhotos.length > 0) {
    try {
      // Use the first uploaded photo
      const productImg = await Jimp.read(uploadedPhotos[0]);
      const imgWidth = 400;
      const imgHeight = 400;
      productImg.resize(imgWidth, imgHeight);
      const xPos = (width / 2) - (imgWidth / 2);
      image.composite(productImg, xPos, yOffset);
      yOffset += imgHeight + 20;
    } catch (err) {
      console.error('Failed to load product image:', err);
    }
  }
  
  // If no photo or photo failed, use a placeholder icon
  if (!uploadedPhotos || uploadedPhotos.length === 0) {
    for (let x = 340; x < 740; x++) {
      for (let y = yOffset; y < yOffset + 200; y++) {
        image.setPixelColor(Jimp.rgbaToInt(102, 126, 234, 255), x, y);
      }
    }
    image.print(fontLarge, 450, yOffset + 70, '📦');
    yOffset += 220;
  }
  
  // Product Name
  let productName = product.name.length > 30 ? product.name.substring(0, 27) + '...' : product.name;
  image.print(fontLarge, 50, yOffset, productName);
  yOffset += 60;
  
  // Price
  const priceText = `KES ${product.price_kes.toLocaleString()}`;
  image.print(fontLarge, 50, yOffset, priceText);
  yOffset += 60;
  
  // Agent Location
  const locationText = `📍 ${agent.location}`;
  image.print(fontMedium, 50, yOffset, locationText);
  yOffset += 50;
  
  // M-Pesa Payment
  const mpesaText = '✓ Pay with M-Pesa';
  image.print(fontMedium, 50, yOffset, mpesaText);
  yOffset += 60;
  
  // Draw button background
  for (let x = 50; x < 350; x++) {
    for (let y = yOffset; y < yOffset + 60; y++) {
      image.setPixelColor(Jimp.rgbaToInt(37, 99, 235, 255), x, y);
    }
  }
  
  // Button text
  const buttonText = 'ORDER NOW';
  image.print(fontBold, 80, yOffset + 15, buttonText);
  yOffset += 80;
  
  // Tracking URL
  const trackingLink = `${baseUrl}/track/${adId}?product=${product.id}&agent=${agent.id}`;
  let displayUrl = trackingLink.length > 60 ? trackingLink.substring(0, 57) + '...' : trackingLink;
  image.print(fontSmall, 50, yOffset, displayUrl);
  
  // Save the image
  await image.writeAsync(outputPath);
  
  return {
    adId: adId,
    imagePath: `/generated_ads/${adId}.png`,
    trackingLink: trackingLink,
    shortUrl: `${baseUrl}/r/${adId}`
  };
}

// Generate carousel ad (3 images) with uploaded photos
async function generateCarouselAd(product, agent, baseUrl, uploadedPhotos = []) {
  const images = [];
  const adId = Date.now().toString() + Math.random().toString(36).substr(2, 6);
  
  for (let i = 1; i <= 3; i++) {
    const outputPath = path.join(adsDir, `carousel_${adId}_${i}.png`);
    const image = new Jimp(1080, 1080, '#ffffff');
    
    let fontLarge, fontMedium, fontBold, fontSmall;
    try {
      fontLarge = await Jimp.loadFont(Jimp.FONT_SANS_64_BLACK);
      fontMedium = await Jimp.loadFont(Jimp.FONT_SANS_32_BLACK);
      fontBold = await Jimp.loadFont(Jimp.FONT_SANS_64_WHITE);
      fontSmall = await Jimp.loadFont(Jimp.FONT_SANS_16_BLACK);
    } catch (err) {
      fontLarge = Jimp.FONT_SANS_64_BLACK;
      fontMedium = Jimp.FONT_SANS_32_BLACK;
      fontBold = Jimp.FONT_SANS_64_WHITE;
      fontSmall = Jimp.FONT_SANS_16_BLACK;
    }
    
    if (i === 1 && uploadedPhotos && uploadedPhotos.length > 0) {
      // First slide - Product image
      try {
        const productImg = await Jimp.read(uploadedPhotos[0]);
        productImg.resize(600, 600);
        const xPos = (1080 / 2) - 300;
        image.composite(productImg, xPos, 200);
      } catch (err) {
        // Fallback to text
        image.print(fontLarge, 300, 400, '📦 PRODUCT');
      }
      image.print(fontMedium, 300, 850, product.name);
      
    } else if (i === 1) {
      // No photo - text only
      let productName = product.name.length > 30 ? product.name.substring(0, 27) + '...' : product.name;
      image.print(fontLarge, 200, 400, productName);
      const priceText = `KES ${product.price_kes.toLocaleString()}`;
      image.print(fontLarge, 200, 550, priceText);
      
    } else if (i === 2) {
      // Second slide - Benefits with agent photo if available
      if (uploadedPhotos && uploadedPhotos.length > 1) {
        try {
          const benefitImg = await Jimp.read(uploadedPhotos[1]);
          benefitImg.resize(400, 400);
          image.composite(benefitImg, 340, 200);
        } catch(e) {}
      }
      image.print(fontMedium, 200, 700, '✓ Free delivery');
      image.print(fontMedium, 200, 770, '✓ Pay with M-Pesa');
      image.print(fontMedium, 200, 840, `✓ Ships from ${agent.location}`);
      
    } else {
      // Third slide - Call to action
      if (uploadedPhotos && uploadedPhotos.length > 2) {
        try {
          const ctaImg = await Jimp.read(uploadedPhotos[2]);
          ctaImg.resize(300, 300);
          image.composite(ctaImg, 390, 200);
        } catch(e) {}
      }
      
      for (let x = 340; x < 740; x++) {
        for (let y = 600; y < 670; y++) {
          image.setPixelColor(Jimp.rgbaToInt(37, 99, 235, 255), x, y);
        }
      }
      image.print(fontBold, 450, 625, 'ORDER NOW');
      
      const trackingLink = `${baseUrl}/track/${adId}?product=${product.id}&agent=${agent.id}`;
      image.print(fontSmall, 50, 950, trackingLink);
    }
    
    await image.writeAsync(outputPath);
    
    images.push({
      imagePath: `/generated_ads/carousel_${adId}_${i}.png`
    });
  }
  
  return {
    adId: adId,
    images: images,
    trackingLink: `${baseUrl}/track/${adId}?product=${product.id}&agent=${agent.id}`
  };
}

module.exports = { generateStaticAd, generateCarouselAd };